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
     * When a customer chooses CredEx on Checkout/Payment page
     *
     */
    public function redirectAction()
    {
        Mage::log('THOMAS: redirectAction');
        //$session = Mage::getSingleton('checkout/session');
        //$session->setCredExStandardQuoteId($session->getQuoteId());
//        $this->getResponse()->setBody($this->getLayout()->createBlock('credex/standard_redirect')->toHtml());
        //$url = $session->getApplicationURL();
        //Mage::log("THOMAS: url $url");
        $this->loadLayout();
        $this->renderLayout();
//        Zend_Debug::dump($this->getLayout()->getUpdate()->getHandles());
//        var_dump(Mage::getSingleton('core/layout')->getUpdate()->getHandles());
//exit("bailing early at ".__LINE__." in ".__FILE__);
//        $this->getResponse()->setBody("Magento welcome to your custom module");
    }

    /**
     * Redirected to from credex box on success
     */
    public function successAction()
    {
        Mage::log('THOMAS: successAction');

        $session = Mage::getSingleton('checkout/session');

        $reservedOrderId = $session->getCredexCustIdExt();
        $order = $this->_convertQuote($reservedOrderId);

        $incrementId = $order->getIncrementId();
        $session->setCredexIncrementId($incrementId);
        Mage::log('THOMAS: got order increment id ' . $incrementId);

//        $state = Mage_Sales_Model_Order::STATE_PROCESSING;
//        $order->setState($state);
//        $order->setStatus('processing');
//        $order->sendNewOrderEmail();
//        $order->save();

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
            Mage::log('credex: transact: ' . $msg);
            $this->_respond($msg . "\n");
            return False;
        }

        return True;
    }

    public function transactAction()
    {
        $request = $this->getRequest();
        // $method = $request->getMethod();

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

        Mage::log('credex: received postback ' .
            $params['function'] . '/' .
            $params['inv_status'] . '/' .
            $params['method'] . ' for inv_id ' .
            $params['inv_id']);

        $order = Mage::getModel('sales/order')
            ->loadByIncrementId($params['cust_id_ext']);
        // FIXME: sanity check that this is the right order with our id ?
        // FIXME: may not have order yet, for purchase/AuthOnly

        if ($params['method'] == 'void') {
            $this->_setState($order,
                Mage_Sales_Model_Order::STATE_CANCELED);
            return;
        } else if ($params['method'] == 'purchase') {
            if ($params['inv_status'] == 'AuthOnly') {
                $order = $this->_convertQuote($params['cust_id_ext']);
                return;
            } else if ($params['inv_status'] == 'Auth') {
                $this->_setState($order,
                    Mage_Sales_Model_Order::STATE_PROCESSING);
                return;
            } else {
                Mage::log('Unknown inv_action ' . $params['inv_action']);
            }
        } else {
            Mage::log('Unknown method ' . $params['method']);
        }


    }

    private function _setState($order, $state) {
        $oldState = $order->getState();
        $order->setState($state);
        $order->setStatus($state);
        Mage::log("THOMAS: changed state from $oldState to $state");
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
            Mage::log('THOMAS: success: already have order with id ' . $reservedOrderId);
            return $order; // FIXME: finish properly
        }


        // new order, so look up quote for this order
        $quote = Mage::getModel("sales/quote")->load(
            $reservedOrderId, 'reserved_order_id');

        Mage::log('THOMAS: convert quote for cust_id_ext ' . $reservedOrderId);

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

        // place sets is to STATE_PROCESSING
        $order->place();
        $this->_setState($order,
            Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW);

        $order->save();
        $order->sendNewOrderEmail();

        $quote->setIsActive(false)->save();

        return $order;
    }
}
