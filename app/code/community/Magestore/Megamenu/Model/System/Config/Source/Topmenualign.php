<?php

class Magestore_Megamenu_Model_System_Config_Source_Topmenualign

{
    //list effect type for fontend
    public function toOptionArray(){
        return array(
            array('value'=>0, 'label'=>'Left'),
            array('value'=>1, 'label'=>'Right'),
            array('value'=>2, 'label'=>'Justify'),
        );
    }
}