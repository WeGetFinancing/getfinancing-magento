<?php
 
/**
* Our test CC module adapter
*/
class EmPayTech_CredEx_Model_PaymentMethod extends Mage_Payment_Model_Method_Abstract
{
    /**
    * unique internal payment method identifier
    *
    * @var string [a-z0-9_]
    */
    protected $_code = 'credex';
 
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
    protected $_canCapture              = true;
 
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
        $errors = FALSE;

        Mage::log("Credex: request to authorize for $ $amount");

        $keys = array("platform", "merch_id", "username", "password");

        foreach ($keys as $key) {
            $$key = $this->getConfigData($key);
            if (!$$key) {
                Mage::log("Credex: specify $key in the configuration");
                $errors = TRUE;
            }
        }

        $platforms = array('staging', 'platform');
        if (!in_array($platform, $platforms)) {
            Mage::log("Credex: platform is set to unsupported value $platform");
            $errors = TRUE;
        }

        if ($errors) $this->misconfigured();

        $key = "gateway_url_$platform";
        $url = $this->getConfigData($key);
        if (!$url) {
            Mage::log("Credex: specify $key in the configuration");
            $errors = TRUE;
        }

        if ($errors) $this->misconfigured();

        $order = $payment->getOrder();

        $billingaddress = $order->getBillingAddress();
        $shippingaddress = $order->getShippingAddress();
        $totals = number_format($amount, 2, '.', '');

        $orderId = $order->getIncrementId();
        $currencyDesc = $order->getBaseCurrencyCode();

        $fields = array(
            'dg_username'=> username,
            'dg_password'=> password,

            'cust_fname'=> $billingaddress->getData('firstname'),
            'cust_lname'=> $billingaddress->getData('lastname'),
            'cust_email'=> $billingaddress->getData('email'),

            'bill_addr_address'=> $billingaddress->getData('street'),
            'bill_addr_city'=> $billingaddress->getData('city'),
            'bill_addr_state'=> $billingaddress->getData('region'),
            'bill_addr_zip'=> $billingaddress->getData('postcode'),

            'ship_addr_address'=> $shippingaddress->getData('street'),
            'ship_addr_city'=> $shippingaddress->getData('city'),
            'ship_addr_state'=> $shippingaddress->getData('region'),
            'ship_addr_zip'=> $shippingaddress->getData('postcode'),

            'version' => '0.3',
            'cust_id_ext'=> $order->getIncrementId(),
            'inv_action' => 'AUTH_ONLY',
            'inv_value' => $totals,
            /* FIXME: description */
            'udf02' => 'Sample purchase'
        );

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,1);
        curl_setopt($ch, CURLOPT_HEADER, 0); // DO NOT RETURN HTTP HEADERS
        curl_setopt($ch, CURLOPT_RETURNTRANSFER ,1); // RETURN THE CONTENTS OF THE CALL
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $result = curl_exec($ch);
        curl_close($ch);

        Mage::log("THOMAS: result: $result");

        $this->parseResponse($result);

        /* throwing an exception makes us stay on this page so we can repeat */
        Mage::throwException(Mage::helper('paygate')->__('Payment capturing error.'));
    }


    private function parseResponse($xmlString)
    {
        $xml = new SimpleXMLElement($xmlString);
        $status_code = $xml->xpath('response/status_code');
        $status_string = $xml->xpath('response/status_string');
        Mage::log("THOMAS: parseResponse: status_code $status_code");
        Mage::log("THOMAS: parseResponse: status_string $status_string");
    }

    private function misconfigured()
    {
        Mage::throwException(Mage::helper('paygate')->__('The web store has misconfigured the Credex payment module.'));
    }

    /* Mage_Payment_Model_Method_Abstract method overrides */
    /**
     * To check billing country is allowed for the payment method
     *
     * @return bool
     */
    public function canUseForCountry($country)
    {
        /* Credex is only available to US customers */
        if ($country == 'US') {
            return true;
        }

        return false;
    }


}

?>
