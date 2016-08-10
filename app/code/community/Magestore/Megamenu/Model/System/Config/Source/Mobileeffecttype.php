<?php

class Magestore_Megamenu_Model_System_Config_Source_Mobileeffecttype

{
    //list effect type for fontend
    public function toOptionArray(){
        return array(
            array('value'=>0, 'label'=>'Slide'),
            array('value'=>1, 'label'=>'Blind'),
        );
    }
}