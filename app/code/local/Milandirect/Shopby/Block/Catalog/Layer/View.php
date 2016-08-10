<?php

/**
 * Remove library jquery
 *
 * @package    Milandirect_Shopby
 * @author     Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2015 Balance
 */
class Milandirect_Shopby_Block_Catalog_Layer_View extends Amasty_Shopby_Block_Catalog_Layer_View
{
    /**
     * Perparelayout before render
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        $pos = Mage::getStoreConfig('amshopby/block/categories_pos');
        if ($this->_notInBlock($pos)) {
            $this->_categoryBlockName = 'amshopby/catalog_layer_filter_empty';
        }
        if (Mage::getStoreConfig('amshopby/general/stock_filter_pos') >= 0) {
            $stockBlock = $this->getLayout()->createBlock('amshopby/catalog_layer_filter_stock')
                ->setLayer($this->getLayer())
                ->init();

            $this->setChild('stock_filter', $stockBlock);
        }

        if (Mage::getStoreConfig('amshopby/general/rating_filter_pos') >= 0) {
            $ratingBlock = $this->getLayout()->createBlock('amshopby/catalog_layer_filter_rating')
                ->setLayer($this->getLayer())
                ->init();
            $this->setChild('rating_filter', $ratingBlock);
        }

        if (Mage::registry('amshopby_layout_prepared')) {
            return parent::_prepareLayout();
        } else {
            Mage::register('amshopby_layout_prepared', true);
        }

        if (!Mage::getStoreConfigFlag('customer/startup/redirect_dashboard')) {
            $url = Mage::helper('amshopby/url')->getFullUrl($_GET);
            Mage::getSingleton('customer/session')
                ->setBeforeAuthUrl($url);
        }

        $head = $this->getLayout()->getBlock('head');
        if ($head) {
            $head->addJs('amasty/amshopby/amshopby.js');

            if (Mage::getStoreConfig('amshopby/block/slider_use_ui')) {
                $head->addJs('amasty/amshopby/jquery-ui.min.js');
                $head->addJs('amasty/amshopby/jquery.ui.touch-punch.min.js');
                $head->addJs('amasty/amshopby/amshopby-jquery.js');
            }

            if (Mage::getStoreConfigFlag('amshopby/block/ajax')) {
                $request = Mage::app()->getRequest();

                $isProductPage = $request->getControllerName() == "product" && $request->getActionName() == "view";

                if (!$isProductPage) {
                    $head->addJs('amasty/amshopby/amshopby-ajax.js');
                }
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getStateHtml()
    {
        $pos = Mage::getStoreConfig('amshopby/block/state_pos');
        if ($this->_notInBlock($pos)) {
            return '';
        }
        $this->getChild('layer_state')->setTemplate('catalog/amshopby/state.phtml');
        return $this->getChildHtml('layer_state');
    }
}