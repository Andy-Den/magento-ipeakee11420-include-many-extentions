<?php

class Magestore_Megamenu_Model_System_Config_Source_Typemenuconfig

{
    //list effect type for fontend
    public function toOptionArray(){
        return array(
            array('value'=>0, 'label'=>'Only PC'),
            array('value'=>1, 'label'=>'Only Mobile'),
            array('value'=>2, 'label'=>'Both PC and Mobile')
        );
    }
}