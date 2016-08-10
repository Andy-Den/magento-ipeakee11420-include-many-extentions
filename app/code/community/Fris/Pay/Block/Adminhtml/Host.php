<?php
/**
 * fris - smart commerce extensions for Magento
 *
 * @category  Fris
 * @package   Fris_Pay
 * @copyright Copyright (c) 2015 fris IT (http://fris.technology)
 * @license   http://fris.technology/license
 * @author    fris IT <support@fris.technology>
 */
class Fris_Pay_Block_Adminhtml_Host extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected $_fieldRenderer;

    protected function _construct()
    {
        parent::_construct();
    }
    
    public function render(Varien_Data_Form_Element_Abstract $element)
    { 
        $hostname = @filter_input(INPUT_SERVER, 'SERVER_NAME');
        if (empty($hostname)) {
            $hostname = $_SERVER['SERVER_NAME'];
        }
        return '
            <tr>
                <td class="label"><label for="' . $element->getHtmlId() . '">' . $element->getLabel() . '</label></td>
                <td class="value" id="hostname">' . htmlspecialchars($hostname) . ' [' . @filter_input(INPUT_SERVER, 'SERVER_ADDR') . ']</td>
            </tr>
        ';
    }

    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_field');
        }
        return $this->_fieldRenderer;
    }
}
