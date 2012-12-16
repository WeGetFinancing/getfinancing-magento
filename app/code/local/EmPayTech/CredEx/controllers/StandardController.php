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
        $credex = new CredEx_Magento($this->getPaymentMethod());

        $session = Mage::getSingleton('checkout/session');
        $quoteId = $session->getQuoteId();
        //Zend_Debug::dump($session);
        Mage::log('THOMAS: do we get YES' . $session->getThomas());
        Mage::log('THOMAS: do we get quoteId' . $session->getQuoteId());
        // FIXME: temporary quote id
        if (!$quoteId) $quoteId = 11;
        $quote = Mage::getModel("sales/quote")->load($quoteId);

        Mage::log("THOMAS: quoteId $quoteId");

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
        $quoteId = $session->getQuoteId();
        $quote = Mage::getModel("sales/quote")->load($quoteId);

        $quote->setIsActive(true)->reserveOrderId();
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

        $order->place();
        $order->save();
        $order->sendNewOrderEmail();

        $quote->setIsActive(false)->save();




//        $quote->setIsActive(false)->save();
        Mage::log('THOMAS: successAction saved quote ' . $quoteId);

        $lastOrderId = Mage::getSingleton('checkout/session')->getLastOrderId();
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

//        $this->_redirect('checkout/onepage/success', array('_secure'=>true));
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

        $order = Mage::getModel('sales/order')->load($params['cust_id_ext']);
        print_r($order);

    }
}
