<?php
/**
 * EmPayTech CredEx module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   EmPayTech
 * @package    EmPayTech_CredEx
 * @copyright  Copyright (c) 2012 EmPayTech
 * @author     Thomas Vander Stichele / EmPayTech <support@empaytech.net>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once 'EmPayTech/CredEx.php';

class EmPayTech_CredEx_StandardController extends Mage_Core_Controller_Front_Action
{
    /**
     * Order instance
     */
    protected $_order;

    /**
     *  Get order
     *
     *  @return   EmPayTech_Sales_Model_Order
     */
    public function getOrder()
    {
        if ($this->_order == null) {
        }
        return $this->_order;
    }

    /**
     * Get singleton with credex strandard order transaction information
     *
     * @return EmPayTech_CredEx_Model_PaymentMethod
     */
    public function getPaymentMethod()
    {
        return Mage::getSingleton('credex/paymentMethod');
    }

    public function requestAction()
    {
        Mage::log('THOMAS: requestAction');

        $session = Mage::getSingleton('checkout/session');
        $quoteId = $session->getQuoteId();
        // FIXME: temporary quote id
        if (!$quoteId) $quoteId = 11;
        $quote = Mage::getModel("sales/quote")->load($quoteId);

        $reservedOrderId = $session->getCredexCustIdExt();
        $quote->setReservedOrderId($reservedOrderId);
        Mage::log('THOMAS: request for cust_id_ext ' . $reservedOrderId);

        $credex = new CredEx_Magento($this->getPaymentMethod());

        $response = $credex->request($quote);

        if ($response->status_code == "APPROVED") {
            $this->_redirect('credex/standard/redirect',
                array('_secure' => true));
        }
    }

    /**
     * When a customer chooses CredEx on Checkout/Payment page,
     * we open up a lightbox with the credex box
     *
     */
    public function redirectAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Redirected to from credex box on success
     */
    public function successAction()
    {
        $session = Mage::getSingleton('checkout/session');

        $reservedOrderId = $session->getCredexCustIdExt();
        $order = $this->_convertQuote($reservedOrderId);

        $this->loadLayout();
        $this->renderLayout();
    }


    private function _respondUnauthorized()
    {
        # FIXME: we need to setHttpResponseCode as well for the unit test to pass
        # http://framework.zend.com/issues/browse/ZF-10374?page=com.atlassian.jira.plugin.system.issuetabpanels:changehistory-tabpanel
        $this->getResponse()
            ->setHttpResponseCode(401)
            ->setRawHeader('HTTP/1.1 401 Unauthorized')
            ->setBody('');
    }

    private function _respond($text)
    {
        $this->getResponse()
            ->setBody($text);
    }

    private function _validateParam($key, $value)
    {
        $allowed = array(
            'function' => array('transact'),
            'inv_status' => array('Auth', 'AuthOnly'),
            'method' => array('void', 'purchase')
        );

        if (! in_array($value, $allowed[$key])) {
            $msg = "unknown $key $value";
            Mage::log('Credex: transact: ' . $msg);
            $this->_respond($msg . "\n");
            return False;
        }

        return True;
    }

    public function transactAction()
    {
        $request = $this->getRequest();

        if (! $request->isPost()) {
            $this->_respondUnauthorized();
            return;
        }

        $params = $request->getPost();

        foreach (array('function', 'inv_status', 'method') as $key) {
            if (! $this->_validateParam($key, $params[$key])) {
                return;
            }
        }

        $postbackMessage = 'Credex: received postback ' .
            $params['function'] . '/' .
            $params['inv_status'] . '/' .
            $params['method'] .
            ' for inv_id ' . $params['inv_id'];

        Mage::log($postbackMessage);

        $order = Mage::getModel('sales/order')
            ->loadByIncrementId($params['cust_id_ext']);
        // FIXME: sanity check that this is the right order with our id ?
        // FIXME: may not have order yet, for purchase/AuthOnly

        $actionMessage = "";

        if ($params['method'] == 'void') {
            if ($order->getState() !=
                Mage_Sales_Model_Order::STATE_CANCELED) {
                $this->_setState($order,
                    Mage_Sales_Model_Order::STATE_CANCELED);
                $actionMessage = 'order canceled by postback.';
            }
        } else if ($params['method'] == 'purchase') {
            if ($params['inv_status'] == 'AuthOnly') {
                $order = $this->_convertQuote($params['cust_id_ext']);
            } else if ($params['inv_status'] == 'Auth') {
                if ($order->getState() !=
                    Mage_Sales_Model_Order::STATE_PROCESSING) {
                    $this->_setState($order,
                        Mage_Sales_Model_Order::STATE_PROCESSING);
                    $actionMessage = 'order loan approved, ready to ship.';
                }
            } else {
                Mage::log('Credex: Unknown inv_action ' . $params['inv_action']);
            }
        } else {
            Mage::log('Credex: Unknown method ' . $params['method']);
        }

        $order->addStatusToHistory($order->getStatus(), $postbackMessage,
            false);
        if ($actionMessage) {
            $order->addStatusToHistory($order->getStatus(),
                "Credex: $actionMessage",
                false);
        }
        $order->save();

    }

    // FIXME: state and status are not the same; status is under state
    private function _setState($order, $state) {
        $oldState = $order->getState();
        $order->setState($state);
        $order->setStatus($state);
        $order->save();
    }

    /*
     * Convert a quote to an order in state payment_review
     * Called on AuthOnly/purchase callback and through onComplete callback
     * from box
     */
    private function _convertQuote($reservedOrderId) // , $custId, $invId)
    {
       // LOOK FOR EXISTING ORDER TO AVOID DUPLICATES
        $order = Mage::getModel('sales/order')->loadByIncrementId($reservedOrderId);
        if ($order->getId()) {
            print_r($order);
            Mage::log('Credex: convertQuote: we already have order with id ' .
                $reservedOrderId);
            return $order; // FIXME: finish properly
        }


        // new order, so look up quote for this order
        $quote = Mage::getModel("sales/quote")->load(
            $reservedOrderId, 'reserved_order_id');
        // FIXME: no way to get persisted additional info
        /*
        $custId = $quote->getCredexCustId();
        $invId = $quote->getCredexInvId();
        $custId = $quote->getAdditionalData('credexCustId');
         */

        Mage::log("Credex: convert quote for cust_id_ext $reservedOrderId " .
        //          ", Credex cust_id $custId, inv_id $invId");
        "");

        // convert quote to order
        $quote->setIsActive(true);
        $quote->getPayment()->importData(array('method'=>'credex'));

        /* see Mage_GoogleCheckout_Model_Api_Xml_Callback */
        $convertQuote = Mage::getSingleton('sales/convert_quote');
        $order = $convertQuote->toOrder($quote);

        if ($quote->isVirtual()) {
            $convertQuote->addressToOrder($quote->getBillingAddress(), $order);
        } else {
            $convertQuote->addressToOrder($quote->getShippingAddress(), $order);
        }

//        $order->setExtOrderId($this->getGoogleOrderNumber());
//        $order->setExtCustomerId($this->getData('root/buyer-id/VALUE'));
        Mage::log('THOMAS: order id ' . $order->getId());

        if (!$order->getCustomerEmail()) {
            $order->setCustomerEmail($billing->getEmail())
                ->setCustomerPrefix($billing->getPrefix())
                ->setCustomerFirstname($billing->getFirstname())
                ->setCustomerMiddlename($billing->getMiddlename())
                ->setCustomerLastname($billing->getLastname())
                ->setCustomerSuffix($billing->getSuffix());
        }

        $order->setBillingAddress($convertQuote->addressToOrderAddress($quote->getBillingAddress()));

        if (!$quote->isVirtual()) {
            $order->setShippingAddress($convertQuote->addressToOrderAddress($quote->getShippingAddress()));
        }


        foreach ($quote->getAllItems() as $item) {
            $orderItem = $convertQuote->itemToOrderItem($item);
            if ($item->getParentItem()) {
                $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
            }
            $order->addItem($orderItem);
        }

        /*
         * Adding transaction for correct transaction information displaying on order view at back end.
         * It has no influence on api interaction logic.
         */
        $payment = Mage::getModel('sales/order_payment')
            ->setMethod('credex')
//            ->setTransactionId($this->getGoogleOrderNumber())
            ->setIsTransactionClosed(false);
        $order->setPayment($payment);
        $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH);

        // place sets state to STATE_PROCESSING
        $order->place();
        $this->_setState($order,
            Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW);

        $order->save();
        $order->sendNewOrderEmail();

        $quote->setIsActive(false)->save();

        /* FIXME: get our credex data
        $custId = $order->getCredexCustId();
        $invId = $order->getCredexInvId();
        */

        Mage::log("Credex: convert to order for cust_id_ext $reservedOrderId " .
        //          ", Credex cust_id $custId, inv_id $invId");
        "");


        /* add order comment */
        $order->addStatusToHistory($order->getStatus(),
            "Credex: order created; hold off shipping for loan verification.",
            false);
        $order->save();

        return $order;
    }
}
