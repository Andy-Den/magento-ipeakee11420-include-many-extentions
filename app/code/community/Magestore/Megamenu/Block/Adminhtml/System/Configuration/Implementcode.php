<?php

class Magestore_Megamenu_Block_Adminhtml_System_Configuration_Implementcode extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element){
        $layout  =  Mage::helper('megamenu')->returnlayout();
        $block = Mage::helper('megamenu')->returnblock();
        $text =  Mage::helper('megamenu')->returntext();
        $template = Mage::helper('megamenu')->returntemplate();
        return '
<div class="entry-edit-head collapseable"><a onclick="Fieldset.toggleCollapse(\'megamenu_template\'); return false;" href="#" id="megamenu_template-head" class="open">Implement Code</a></div>
<input id="megamenu_template-state" type="hidden" value="1" name="config_state[megamenu_template]">
<fieldset id="megamenu_template" class="config collapseable" style="">
    <div id="messages" class="div-mess-megamenu">
        <ul class="messages mess-megamennu">
            <li class="notice-msg notice-megamenu">
                <ul>
                    <li>
                    '.$text.'
                    </li>				
                </ul>
            </li>
        </ul>
    </div>
    <br/>  
    <div id="messages" class="div-mess-megamenu">
        <ul class="messages mess-megamennu">
            <li class="notice-msg notice-megamenu">
                <ul>
                    <li>
                    '.Mage::helper('megamenu')->__('Option 1: Add code below to a CMS Page or a Static Block').'
                    </li>
                </ul>
            </li>
        </ul>
    </div>
        <ul>
            <li>
                <code>
                '.$block.'
                </code>	
            </li>
        </ul>     
    <br/>
    <div id="messages" class="div-mess-megamenu">
       <ul class="messages mess-megamennu">
            <li class="notice-msg notice-megamenu">
                <ul>
                    <li>
                    '.Mage::helper('megamenu')->__('Option 2: Add code below to a template file').'
                    </li>
                </ul>
            </li>
        </ul>
    </div>
    <ul>
        <li>
            <code>
            '.$template.'
            </code>	
        </li>
    </ul>
    <br/>
    <div id="messages" class="div-mess-megamenu">
        <ul class="messages mess-megamennu">
            <li class="notice-msg notice-megamenu">
                <ul>
                    <li>
                    '.Mage::helper('megamenu')->__('Option 3: Add code below to a layout file').'
                    </li>
                </ul>
            </li>
        </ul>
    </div>
    <ul>
        <li>
            <code>
            '.$layout.'
            </code>	
        </li>
    </ul>
</fieldset>';
    }
}
