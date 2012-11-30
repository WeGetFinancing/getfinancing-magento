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
 * @package    EmPayTech_Credex
 * @copyright  Copyright (c) 2012 EmPayTech
 * @author     Thomas Vander Stichele / EmPayTech <support@empaytech.net>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @doNotIndexAll
 */

class EmPayTech_Credex_Test_Model_PaymentMethod extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @var EmPayTech_CredEx_Model_PaymentMethod
     */
    protected $model = null;

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
}
