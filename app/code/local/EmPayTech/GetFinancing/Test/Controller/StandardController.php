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

/**
 * @doNotIndexAll
 */
class EmPayTech_GetFinancing_Test_Controller_StandardController extends EcomDev_PHPUnit_Test_Case_Controller
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
     * @covers EmPayTech_GetFinancing_StandardController::redirectAction
     */
    public function testDispatch()
    {
        $this->dispatch('getfinancing/standard/redirect');

        $this->assertRequestRoute('getfinancing/standard/redirect');

        $this->assertLayoutBlockCreated('left');
        $this->assertLayoutBlockCreated('right');
        $this->assertLayoutBlockRendered('content');
        $this->assertLayoutBlockTypeOf('left', 'core/text_list');
        $this->assertLayoutBlockNotTypeOf('left', 'core/links');

        /* check if our custom layout is loaded */
        $this->assertLayoutHandleLoaded('getfinancing_standard_redirect');
        $layout = $this->getLayout();
        $this->assertTrue($layout->isLoaded());
        // FIXME: anything else we can do to verify whether our getfinancing
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

    public function testTransactGet()
    {
        $request = $this->getRequest();


        $this->dispatch('getfinancing/standard/transact');

        $this->assertRequestRoute('getfinancing/standard/transact');

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

    private function _post($data)
    {
        $request = $this->getRequest();
        $request->setMethod('POST');
        parse_str($data, $params);
        foreach ($params as $key => $value) {
            $request->setPost($key, $value);
        }


    }

    public function testTransactPostWrongFunction()
    {
        $post = 'function=unknown&inv_status=Auth&method=void&inv_id=0991dfd815ad17e530d88728ce046c4c&crl_id=646db422798711e19d620026822d76da&version=0.3';
        $this->_post($post);

        $this->dispatch('getfinancing/standard/transact');

        $this->assertRequestRoute('getfinancing/standard/transact');

        $this->assertResponseHttpCode(200);
        $this->_assertResponseBodyIs("unknown function unknown\n");

        return $this;
    }

    /**
     * @loadFixture
     */
    public function testTransactPost()
    {
        $this->app()->disableEvents();

        $post = 'function=transact&inv_status=Auth&method=void&inv_id=0991dfd815ad17e530d88728ce046c4c&crl_id=646db422798711e19d620026822d76da&version=0.3&cust_id_ext=1';
        $this->_post($post);

        $this->dispatch('getfinancing/standard/transact');

        $this->assertRequestRoute('getfinancing/standard/transact');

        $this->assertResponseHttpCode(200);
        $this->_assertResponseBodyIs('');

        return $this;
        $this->app()->enableEvents();
    }
}
