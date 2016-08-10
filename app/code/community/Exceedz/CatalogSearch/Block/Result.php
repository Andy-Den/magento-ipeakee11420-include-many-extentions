<?php
/**
 * Product search result block
 *
 * @category   Exceedz
 * @package    Exceedz_CatalogSearch
 * @module     Catalog
 */
class Exceedz_CatalogSearch_Block_Result extends Mage_CatalogSearch_Block_Result
{
    /**
     * Prepare layout
     *
     * @return Exceedz_CatalogSearch_Block_Result
     */
    protected function _prepareLayout()
    {
        // add Home breadcrumb
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
           $breadcrumbs->addCrumb('home', array(
                'label' => $this->__('Home'),
                'title' => $this->__('Go to Home Page'),
                'link'  => Mage::getBaseUrl()
            ))->addCrumb('search', array(
                'label' => $this->__("Search Results"),
                'title' => $this->__("Search Results")
            ));
        }

        // modify page title
        $title = $this->__("Search results for: '%s'", $this->helper('catalogsearch')->getEscapedQueryText());
        $this->getLayout()->getBlock('head')->setTitle($title);
    }   
}