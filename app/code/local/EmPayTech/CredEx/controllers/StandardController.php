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
}
