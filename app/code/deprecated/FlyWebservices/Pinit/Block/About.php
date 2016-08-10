<?php

class FlyWebservices_Pinit_Block_About
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = <<<HTML
<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=38C3P9J3WEJWL" target="_blank">
<img src="https://www.paypalobjects.com/en_US/NL/i/btn/btn_donateCC_LG.gif" border="0" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/nl_NL/i/scr/pixel.gif" width="1" height="1">
</a>
HTML;
        return $html;
    }
}