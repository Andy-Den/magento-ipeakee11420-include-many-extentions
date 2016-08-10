<?php

class Balance_Exclusion_IndexController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $id = $this->getRequest()->getParam('id');
        Mage::register('exclusion_', $id);


        $this->loadLayout();
        $this->renderLayout();
    }

}