<?php

class Balance_Exclusion_Block_Exclusion extends Mage_Core_Block_Template
{

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getExclusion()
    {
        if (!$this->hasData('exclusion')) {
            $this->setData('exclusion', Mage::registry('exclusion'));
        }

        return $this->getData('exclusion');
    }

}

