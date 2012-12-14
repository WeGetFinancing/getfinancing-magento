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
 */
class EmPayTech_Credex_Test_Controller_StandardController extends EcomDev_PHPUnit_Test_Case_Controller
{
    // FIXME: assertResponseBodyContains does not seem to work
    public function _assertResponseBodyContains($string)
    {
        $body = $this->getResponse()->getOutputBody();
        $constraint = $this->stringContains($string);
        $this->assertThat($body, $constraint);
    }

    // FIXME: neither can I figure out how to use RegExp
    public function _assertResponseBodyIs($string)
    {
        $body = $this->getResponse()->getOutputBody();
        $constraint = $this->equalTo($string);
        $this->assertThat($body, $constraint);
    }


    /**
     * Test for canUseForCountry
     *
     * @covers EmPayTech_CredEx_StandardController::redirectAction
     */
    public function testDispatch()
    {
        $this->dispatch('credex/standard/redirect');

        $this->assertRequestRoute('credex/standard/redirect');

        $this->assertLayoutBlockCreated('left');
        $this->assertLayoutBlockCreated('right');
        $this->assertLayoutBlockRendered('content');
        $this->assertLayoutBlockTypeOf('left', 'core/text_list');
        $this->assertLayoutBlockNotTypeOf('left', 'core/links');

        /* check if our custom layout is loaded */
        $this->assertLayoutHandleLoaded('credex_standard_redirect');
        $layout = $this->getLayout();
        $this->assertTrue($layout->isLoaded());
        // FIXME: anything else we can do to verify whether our credex
        //        template gets loaded ?
        // $records = $layout->getRecords();
        // print Zend_Debug::dump($records['handle_loaded']);

        // FIXME: this doesn't work, so for now I work around it
        //$this->assertResponseBodyContains('Magento');
        $this->_assertResponseBodyContains('Magento');
        // FIXME: in a test, my template does not get invoked
        //$this->_assertResponseBodyContains('Welcome to your custom module');

        return $this;
    }

    public function testTransact()
    {
        $request = $this->getRequest();


        $this->dispatch('credex/standard/transact');

        $this->assertRequestRoute('credex/standard/transact');

        //$response = $this->getResponse();

        // FIXME: we get 200 instead of 401 even if setRawHeader was called
        //        unless the Action also calls setHttpResponseCode
        $this->assertResponseHttpCode(401);
        $body = $this->getResponse()->getOutputBody();
        // FIXME: no idea how to make RegExp work
        // $this->assertResponseBodyRegExp('/^$/');
        $this->_assertResponseBodyIs('');

        return $this;
    }
}
