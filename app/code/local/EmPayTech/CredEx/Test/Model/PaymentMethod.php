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

/**
 * @doNotIndexAll
 * @loadFixture config
 */

require_once 'EmPayTech/CredEx.php';

class EmPayTech_Credex_Test_Model_PaymentMethod extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @var EmPayTech_CredEx_Model_PaymentMethod
     */
    protected $model = null;
    protected $credex = null;

    /**
     * Initializes model under test and disables events
     * for checking logic in isolation from custom its customizations
     *
     * (non-PHPdoc)
     * @see EcomDev_PHPUnit_Test_Case::setUp()
     */
    protected function setUp()
    {
        parent::setUp();
        $this->model = Mage::getModel('credex/paymentMethod');
        $this->app()->disableEvents();

        $this->credex = new CredEx_Magento($this->model);
    }

    /**
     * Enables events back
     * (non-PHPdoc)
     * @see EcomDev_PHPUnit_Test_Case::tearDown()
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->app()->enableEvents();
    }

    /**
     * Test for canUseForCountry
     *
     * @covers EmPayTech_CredEx_Model_PaymentMethod::canUseForCountry
     */
    public function testCanUseForCountry()
    {
        $this->assertEquals(
            true,
            $this->model->canUseForCountry('US')
        );
        $this->assertEquals(
            false,
            $this->model->canUseForCountry('BE')
        );

        return $this;
    }

    /**
     * Test for parseResponse
     *
     * @covers EmPayTech_CredEx_Model_PaymentMethod::parseResponse
     */
    public function testParseResponseNoAccepts()
    {
        $xmlString = <<<EOT
<?xml version='1.0' encoding='utf-8'?>
<response><status_code>GW_UNSUPPORTED_FORMAT</status_code><status_string><![CDATA[Unsupported response format None. The only format supported is XML]]></status_string></response>
EOT;

        $this->assertEquals(
            "GW_UNSUPPORTED_FORMAT",
            $this->credex->parseResponse($xmlString)->status_code
        );

        return $this;
    }

    /**
     * Test for parseResponse
     *
     * @covers EmPayTech_CredEx_Model_PaymentMethod::parseResponse
     */
    public function testParseResponseAuthenticationFailed()
    {
        $xmlString = <<<EOT
<?xml version='1.0' encoding='utf-8'?>
<response><status_code>GW_NOT_AUTH</status_code><status_string><![CDATA[Authentication failed]]></status_string></response>
EOT;

        $this->assertEquals(
            "GW_NOT_AUTH",
            $this->model->parseResponse($xmlString)->status_code
        );

        return $this;
    }

    /**
     * Test for parseResponse
     *
     * @covers EmPayTech_CredEx_Model_PaymentMethod::parseResponse
     */
    public function testParseResponseMissingField()
    {
        $xmlString = <<<EOT
<?xml version='1.0' encoding='utf-8'?>
<response><status_code>GW_MISSING_FIELD</status_code><status_string><![CDATA['cust_email']]></status_string></response>
EOT;

        $this->assertEquals(
            "GW_MISSING_FIELD",
            $this->model->parseResponse($xmlString)->status_code
        );

        return $this;
    }

    /**
     * Test for parseResponse
     *
     * @covers EmPayTech_CredEx_Model_PaymentMethod::parseResponse
     */
    public function testParseSuccessful()
    {
        $xmlString = <<<EOT
<?xml version='1.0' encoding='utf-8'?>
<response><status_code>APPROVED</status_code><status_string><![CDATA[]]></status_string><udf02>https://partner-test.credex.net/partner-test/lc/info/a375a42a899ad1c3e3267888fd8173f7</udf02><cust_id>a375a42a899ad1c3e3267888fd8173f7</cust_id><inv_value_requested>5005.00</inv_value_requested><inv_id>a375a42a899ad1c3e3267888fd8173f7</inv_id></response>
EOT;

        $this->assertEquals(
            "APPROVED",
            $this->model->parseResponse($xmlString)->status_code
        );
        $this->assertEquals(
            "5005.00",
            $this->model->parseResponse($xmlString)->inv_value_requested
        );


        return $this;
    }
}
?>
