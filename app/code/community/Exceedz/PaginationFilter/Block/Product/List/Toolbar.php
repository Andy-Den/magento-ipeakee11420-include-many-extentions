<?php
/**
 * Product list toolbar
 *
 * @category    Exceedz
 * @package     Exceedz_PaginationFilter
 */
class Exceedz_PaginationFilter_Block_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{
    /**
     * Set collection to pager
     *
     * @param Varien_Data_Collection $collection
     * @return Mage_Catalog_Block_Product_List_Toolbar
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection;

        if($this->_isPaginationEnabled()) {
        	$this->_collection->setCurPage($this->getCurrentPage());

        	// we need to set pagination only if passed value integer and more that 0
        	$limit = (int)$this->getLimit();
        	if ($limit) {
            	$this->_collection->setPageSize($limit);
        	}
        }
        if ($this->getCurrentOrder()) {
            $this->_collection->setOrder($this->getCurrentOrder(), $this->getCurrentDirection());
        }
        return $this;
    }

    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
    	if(!$this->_isPaginationEnabled()) return '';

        $pagerBlock = $this->getChild('product_list_toolbar_pager');

        if ($pagerBlock instanceof Varien_Object) {

            /* @var $pagerBlock Mage_Page_Block_Html_Pager */
            $pagerBlock->setAvailableLimit($this->getAvailableLimit());

            $pagerBlock->setUseContainer(false)
                ->setShowPerPage(false)
                ->setShowAmounts(false)
                ->setLimitVarName($this->getLimitVarName())
                ->setPageVarName($this->getPageVarName())
                ->setLimit($this->getLimit())
                ->setFrameLength(Mage::getStoreConfig('design/pagination/pagination_frame'))
                ->setJump(Mage::getStoreConfig('design/pagination/pagination_frame_skip'))
                ->setCollection($this->getCollection());

            return $pagerBlock->toHtml();
        }
    }

    /*
     * Check for pagination enabled or not
     * @return boolean
     */
    private function _isPaginationEnabled()
    {
    	if(is_object(Mage::registry('current_category'))) {
    		return Mage::registry('current_category')->getEnablePagination();
    	} elseif($this->getRequest()->getModuleName() == 'catalogsearch') {
    		return true;
    	}

    	return false;
    }
}
