<?php

class Balance_ConnectFurniture_Adminhtml_Connectfurniture_ExportController extends Mage_Adminhtml_Controller_action
{
    public function indexAction()
    {
        Mage::helper('connectfurniture')->exportFeed();
        $this->_redirectUrl(Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit', array('_current'  => true,'section' => 'connectfurniture')));
        return false;
    }
}