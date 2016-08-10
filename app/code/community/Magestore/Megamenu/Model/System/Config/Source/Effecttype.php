<?php

class Magestore_Megamenu_Model_System_Config_Source_Effecttype

{
    //list effect type for fontend
    public function toOptionArray(){
        return array(
            array('value'=>1, 'label'=>'Hover'),
            array('value'=>2, 'label'=>'Animate'),
            array('value'=>3, 'label'=>'Toggle')
        );
    }
}