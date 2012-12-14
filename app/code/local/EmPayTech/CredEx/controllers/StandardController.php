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
        return Mage::getSingleton('credex/paymentmethod');
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

    private function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    public function failureAction()
    {
        Mage::log('THOMAS: failureAction');
        $lastQuoteId = $this->getOnePage()->getCheckout()->getLastQuoteId();
        $lastOrderId = $this->getOnePage()->getCheckout()->getLastOrderId();
        Mage::log("THOMAS: failureAction: last quote $lastQuoteId, last order $lastOrderId");
        if ($lastQuoteId && $lastOrderId) {
            $orderModel = Mage::getModel('sales/order')->load($lastOrderId);
            if($orderModel->canCancel()) {
                $quote = Mage::getModel('sales/quote')->load($lastQuoteId);
                $quote->setIsActive(true)->save();
                $orderModel->cancel();
                $orderModel->setStatus('canceled');
                $orderModel->save();
                Mage::getSingleton('core/session')->setFailureMsg('order_failed');
                Mage::getSingleton('checkout/session')->setFirstTimeChk('0');
                $this->_redirect('checkout/index/payment', array("_forced_secure" => true));
                return;
            }
        }
        if (!$lastQuoteId || !$lastOrderId) {
            $this->_redirect('checkout/cart', array("_forced_secure" => true));
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }
}
