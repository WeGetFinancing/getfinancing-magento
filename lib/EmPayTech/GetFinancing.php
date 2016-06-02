<?php
/**
 * EmPayTech GetFinancing module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   EmPayTech
 * @package    EmPayTech_GetFinancing
 * @copyright  Copyright (c) 2012 EmPayTech
 * @author     Thomas Vander Stichele / EmPayTech <support@empaytech.net>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class GetFinancing
{
    /**
     * @param string $url      url to post requests to (ends in transact.cfm)
     * @param string $merch_id merchant id
     * @param string $username
     * @param string $password
     */
    public function __construct($url, $merch_id, $username, $password) {
        $this->url = $url;
        $this->merch_id = $merch_id;
        $this->_username = $username;
        $this->_password = $password;
    }

    public function getDataspace() {
        if (strpos($this->url, 'api-test') !== false) {
            return 'staging';
        } else {
            return 'production';
        }
    }

    public function log($msg) {
    }

    function request($fields) {
        $fields = array_merge($fields, array(
            'dg_username'=> $this->_username,
            'dg_password'=> $this->_password,
            'merch_id'=> $this->merch_id));

        $query = http_build_query($fields);
        $this->log("request: $query");
        $this->log("url to post: $this->url");

        $ch = curl_init($this->url);

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSLVERSION, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,1);
        // FIXME: we have to set response_format
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        //    "Accept: application/xml"
        //));
//        curl_setopt($ch, CURLOPT_HEADER, 0); // DO NOT RETURN HTTP HEADERS
        /* return contents of the call as a variable; otherwise it prints */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

        $result = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $msg = "curl_exec error: $error";
            $this->log($msg);
            Mage::throwException("GetFinancing: $msg");
        }

        $response = $this->parseResponse($result);

        $this->log('response: ' . $response->asXML());
        return $response;
    }

    public function parseResponse($xmlString)
    {
        /* SimpleXMLElement returns an element proxying for the root <response>
           tag */
        $response = new SimpleXMLElement($xmlString);

        return $response;
    }

}

class GetFinancing_Magento extends GetFinancing
{
    public function __construct($paymentMethod) {

        $this->configured = FALSE;

        $errors = FALSE;
        $message = "";

        $keys = array("platform", "merch_id", "username", "password");

        foreach ($keys as $key) {
            $$key = $paymentMethod->getConfigData($key);
            if (!$$key) {
                $message = "Please specify '$key' in the configuration.";
                $errorkey = $key;
                $errors = TRUE;
            }
        }

        $platforms = array('staging', 'production');
        if (!in_array($platform, $platforms)) {
            $message = "platform is set to unsupported value '$platform'";
            $errorkey = 'platform';
            $errors = TRUE;
        }

        if ($errors) {
            $this->log($message);
            $this->misconfigured($message, $errorkey);

            /* FIXME: can we return half-constructed ? */
            return;
        }

        $this->configured = TRUE;

        parent::__construct($url, $merch_id, $username, $password);
    }

    /* parent class implementations */
    public function log($msg) {
        Mage::log("GetFinancing: $msg");
    }

    private function misconfigured($message, $option = "")
    {
        if ($option)
            $option = " (option '$option')";
//        Mage::throwException(Mage::helper('paygate')->__("The web store has misconfigured the GetFinancing payment module${option}."));
        $this->_adminnotification(
            $title          = Mage::helper('getfinancing')->__('GetFinancing: the payment method is misconfigured.%s', $option),
            $description    = $message
        );
    }

    /*
     * Mage_Sales_Model_Quote
     */
    public function request($quote)
    {
        $errors = FALSE;

        $totals = number_format($quote->getGrandTotal(), 2, '.', '');
        $cust_id_ext = $quote->reserveOrderId()->getReservedOrderId();

        $this->log("request: new request for total amount $totals"
                   . " and cust_id_ext $cust_id_ext");

        $version = Mage::getVersion();
        $this->log("request: Magento version: $version");

        $billingaddress = $quote->getBillingAddress();
        $shippingaddress = $quote->getShippingAddress();

        /* FIXME: Magento 1.4.0.1 seems to not have getCustomerEmail ? */
        $this->log("request: billing  email " . $billingaddress->getEmail());
        $this->log("request: shipping email " . $shippingaddress->getEmail());
        $this->log("request: quote    email " . $quote->getCustomerEmail());

        $sparse_array = array(
            $billingaddress->getEmail(),
            $shippingaddress->getEmail(),
            $quote->getCustomerEmail());
        /* array_filter keeps truthy values, so throws out empty strings */
        $email = current(array_filter($sparse_array));

        $this->log("request: Got email $email");

        $description = "";
        $cartHelper = Mage::helper('checkout/cart');
        $items = $cartHelper->getCart()->getItems();

        foreach ($items as $item) {
            $description .= $item->getName() . " (" . $item->getQty() . "), ";
        }
        $description = substr($description, 0, -2);
        $this->log("request: description " . $description);

        //version 1.9
        $gf_data = array(
            'amount'           => $totals,
            'product_info'     => $description,
            'first_name'       => $billingaddress->getData('firstname'),
            'last_name'        => $billingaddress->getData('lastname'),
            'shipping_address' => array(
                'street1'  => $billingaddress->getData('street'),
                'city'    => $billingaddress->getData('city'),
                'state'   => $billingaddress->getData('region'),
                'zipcode' => $billingaddress->getData('postcode')
            ),
            'billing_address' => array(
                'street1'  => $shippingaddress->getData('street'),
                'city'    => $shippingaddress->getData('city'),
                'state'   => $shippingaddress->getData('region'),
                'zipcode' => $shippingaddress->getData('postcode')
            ),
            'email'            => $email,
            'phone'            => $billingaddress->getData('telephone'),
            'merchant_loan_id' => (string)$cust_id_ext,
            'version' => '1.9',
            'software_name' => 'magento',
            'software_version' => $version
        );

        $paymentMethod = Mage::getSingleton('getfinancing/paymentMethod');
        $username = $paymentMethod->getConfigData('username');
        $password = $paymentMethod->getConfigData('password');

        $body_json_data = json_encode($gf_data);
        $header_auth = base64_encode($username . ":" . $password);


        if ($paymentMethod->getConfigData('platform') == "staging") {
            $url_to_post = 'https://api-test.getfinancing.com/merchant/';
        } else {
            $url_to_post = 'https://api.getfinancing.com/merchant/';
        }

        $url_to_post .= $paymentMethod->getConfigData('merch_id')  . '/requests';

        $post_args = array(
            'body' => $body_json_data,
            'timeout' => 60,     // 60 seconds
            'blocking' => true,  // Forces PHP wait until get a response
            'sslverify' => false,
            'headers' => array(
              'Content-Type' => 'application/json',
              'Authorization' => 'Basic ' . $header_auth,
              'Accept' => 'application/json'
             )
        );

        $gf_response = $this->_remote_post( $url_to_post, $post_args );

        if ($gf_response === false) {
          $message = "GF connection failure";
          $this->log("request: $post_args");
          Mage::throwException($message);
        }

        $gf_response = json_decode($gf_response);

        $this->getSession()->setGetFinancingApplicationURL((string) $gf_response->href);
        //$this->getSession()->setGetFinancingCustId((string) $gf_response->customer_id);
        $this->getSession()->setGetFinancingInvId((string) $gf_response->inv_id);


        return $gf_response;
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

    /**
     * Set up RemotePost / Curl.
     */
    function _remote_post($url,$args=array()) {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $args['body']);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Magento - GetFinancing Payment Module ');
        if (defined('CURLOPT_POSTFIELDSIZE')) {
            curl_setopt($curl, CURLOPT_POSTFIELDSIZE, 0);
        }
        curl_setopt($curl, CURLOPT_TIMEOUT, $args['timeout']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        $array_headers = array();
        foreach ($args['headers'] as $k => $v) {
            $array_headers[] = $k . ": " . $v;
        }
        if (sizeof($array_headers)>0) {
          curl_setopt($curl, CURLOPT_HTTPHEADER, $array_headers);
        }

        if (strtoupper(substr(@php_uname('s'), 0, 3)) === 'WIN') {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }

        $resp = curl_exec($curl);
        curl_close($curl);

        if (!$resp) {
          return false;
        } else {
          return $resp;
        }
    }


    /**
     * Create a Magento admin notification.
     */
    private function _adminnotification(
        $title,
        $description,
        $severity = Mage_AdminNotification_Model_Inbox::SEVERITY_MAJOR
    )
    {
        $this->log("creating admin notification $title");

        $date = date('Y-m-d H:i:s');
        /* We use parse because it's the only thing 1.4.0.1 has */
        /* FIXME: can we set url to the payment method config ? */
        Mage::getModel('adminnotification/inbox')->parse(array(
            array(
                'severity'      => $severity,
                'date_added'    => $date,
                'title'         => $title,
                'description'   => $description,
                'url'           => '',
#                'url' => Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit/section/payment'),
                'internal'      => true
            )
        ));
    }

}
