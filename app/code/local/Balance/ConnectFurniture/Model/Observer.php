<?php

class Balance_ConnectFurniture_Model_Observer
{
    public function exportFeed()
    {
        Mage::helper('connectfurniture')->exportFeed();
    }
}