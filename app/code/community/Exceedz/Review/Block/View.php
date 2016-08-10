<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Review
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Review detailed view block
 *
 * @category   Mage
 * @package    Mage_Review
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Exceedz_Review_Block_View extends Mage_Review_Block_View
{
	/**
     * prepare breadcrumb
     */
    protected function _prepareLayout(){
    	$url  = Mage::getBaseUrl()."".$this->getProductData()->getUrlPath();
         if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')){
            $breadcrumbs->addCrumb('home', array('label'=>__('Home'), 'title'=>__('Go to Home Page'), 'link'=>Mage::getBaseUrl()));
            $breadcrumbs->addCrumb($this->getProductData()->getName(), array('label'=>$this->getProductData()->getName(), 'title'=>$this->getProductData()->getName(), 'link'=>$url));
            $breadcrumbs->addCrumb('Review Details', array('label'=>'Review Details', 'title'=>'Review Details'));
         }
    }
    
    /**
     * Retrieve collection of ratings
     *
     * @return Mage_Rating_Model_Mysql4_Rating_Option_Vote_Collection
     */
    public function getCustomerRating($reviewId)
    {
        if( !$this->getRatingCollection() ) {
            $ratingCollection = Mage::getModel('rating/rating_option_vote')
                ->getResourceCollection()
                ->setReviewFilter($reviewId)
                ->setStoreFilter(Mage::app()->getStore()->getId())
                ->addRatingInfo(Mage::app()->getStore()->getId())
                ->load();
            $this->setRatingCollection( ( $ratingCollection->getSize() ) ? $ratingCollection : false );
        }
        return $this->getRatingCollection();
    }
}
