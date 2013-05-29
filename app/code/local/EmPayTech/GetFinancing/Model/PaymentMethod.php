<?php

/**
* Our test CC module adapter
*/
class EmPayTech_GetFinancing_Model_PaymentMethod extends Mage_Payment_Model_Method_Abstract
{
    /**
    * unique internal payment method identifier
    *
    * @var string [a-z0-9_]
    */
    protected $_code = 'getfinancing';

    /**
     * Here are examples of flags that will determine functionality availability
     * of this module to be used by frontend and backend.
     *
     * @see all flags and their defaults in Mage_Payment_Model_Method_Abstract
     *
     * It is possible to have a custom dynamic logic by overloading
     * public function can* for each flag respectively
     */

    /**
     * Is this payment method a gateway (online auth/charge) ?
     */
    protected $_isGateway               = true;

    /**
     * Can authorize online?
     */
    protected $_canAuthorize            = true;

    /**
     * Can capture funds online?
     */
    protected $_canCapture              = false;

    /**
     * Can capture partial amounts online?
     */
    protected $_canCapturePartial       = false;

    /**
     * Can refund online?
     */
    protected $_canRefund               = false;

    /**
     * Can void transactions online?
     */
    protected $_canVoid                 = true;

    /**
     * Can use this payment method in administration panel?
     */
    protected $_canUseInternal          = true;

    /**
     * Can show this payment method as an option on checkout payment page?
     */
    protected $_canUseCheckout          = true;

    /**
     * Is this payment method suitable for multi-shipping checkout?
     */
    protected $_canUseForMultishipping  = true;

    /**
     * Can save credit card information for future processing?
     */
    protected $_canSaveCc = false;

    /**
     * Here you will need to implement authorize, capture and void public methods
     *
     * @see examples of transaction specific public methods such as
     * authorize, capture and void in Mage_Paygate_Model_Authorizenet
     */

    /**
     * Send authorize request to gateway
     *
     * @param  Varien_Object $payment
     * @param  decimal $amount
     * @return Mage_Paygate_Model_Authorizenet
     * @throws Mage_Core_Exception
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        Mage::log("GetFinancing: request to authorize for $ $amount");
    }


    /* Mage_Payment_Model_Method_Abstract method overrides */
    /**
     * To check billing country is allowed for the payment method
     *
     * @return bool
     */
    public function canUseForCountry($country)
    {
        /* GetFinancing is only available to US customers */
        if ($country == 'US') {
            return true;
        }

        return false;
    }


    /**
     * Return Order place redirect url
     *
     * @return string
     */
    /*
     * http://stackoverflow.com/questions/9185129/magento-payment-redirect-order
     * If a payment method model implements getOrderPlaceRedirectUrl() the
     * customer will be redirected after the confirmation step of the one page
     * checkout, the order entity will be created.

     * If a payment method model implements the getCheckoutRedirectUrl()
     * method, the customer will be redirected after the payment step of the
     * one page checkout, and no order entity is created.
     */
    public function getCheckoutRedirectUrl()
//    public function getOrderPlaceRedirectUrl()
    {
        $quoteId = $this->getSession()->getQuoteId();
        $quote = Mage::getModel("sales/quote")->load($quoteId);

        // reserve an order id and set it as cust_id_ext to use as the
        // reference for getfinancing everywhere
        $orderId = $quote->reserveOrderId()->getReservedOrderId();
        $quote->save();
        $this->getSession()->setGetFinancingCustIdExt($orderId);

        Mage::log('GetFinancing: getCheckout: reserved order id ' . $orderId);
        return Mage::getUrl('getfinancing/standard/request',
            array('_secure' => true));
    }

     /**
     * Get getfinancing session namespace
     *
     * @return Mage_GetFinancing_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('checkout/session');
    }
}

?>
