<?php

/**
 * Pagantis_Pagantis payment form
 *
 * @package    Pagantis_Pagantis
 * @copyright  Copyright (c) 2015 Yameveo (http://www.yameveo.com)
 * @author	   Yameveo <yameveo@yameveo.com>
 * @link	   http://www.yameveo.com
 */
class EmPayTech_GetFinancing_Adminhtml_Model_System_Config_Source_Payment
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{

    /**
     * Return header comment part of html for payment solution
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getHeaderCommentHtml($element)
    {
            return '<div class="adminLogoGF">
              <p class="adminpGF">GetFinancing is an online Purchase Finance Gateway. Choose GetFinancing as your payment gateway to get access to multiple lenders in a powerful platform.</p>
              <p class="adminpGF"><a href="https://partner.getfinancing.com/partner/portal" target="_blank">Login for your GetFinancing Account</a>&nbsp;
              <a href="https://www.getfinancing.com/docs" target="_blank">Documentation</a></p></div>';
    }

}
