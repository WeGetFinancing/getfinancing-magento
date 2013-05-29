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
class EmPayTech_GetFinancing_Test_Config_Main extends EcomDev_PHPUnit_Test_Case_Config
{
    public function testConfig()
    {
        $this->assertLayoutFileDefined('frontend', 'empaytech.xml');
        /* changing getfinancing to some unknown string will dump the xml
         * representation for easier debugging */
        $this->assertLayoutFileDefined('frontend', 'empaytech.xml', 'getfinancing');
    }
}
