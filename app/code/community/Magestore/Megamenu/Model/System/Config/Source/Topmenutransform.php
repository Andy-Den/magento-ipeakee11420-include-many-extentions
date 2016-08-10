<?php

class Magestore_Megamenu_Model_System_Config_Source_Topmenutransform

{
    //list effect type for fontend
    public function toOptionArray(){
        return array(
            array('value'=>'none', 'label'=>'Normal'),
            array('value'=>'uppercase', 'label'=>'Uppercase'),
            array('value'=>'lowercase', 'label'=>'Lowercase'),
            array('value'=>'capitalize', 'label'=>'Capitalize'),
        );
    }
}