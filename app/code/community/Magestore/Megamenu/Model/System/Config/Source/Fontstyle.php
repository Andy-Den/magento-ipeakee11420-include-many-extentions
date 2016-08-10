<?php

class Magestore_Megamenu_Model_System_Config_Source_Fontstyle
{
    public function toOptionArray(){
       return Mage::helper('megamenu')->getFontStyle();
    }
}