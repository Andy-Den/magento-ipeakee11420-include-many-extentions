<?php

/**
 * Rewrite Review list to get product
 *
 * @category  Milandirect
 * @package   Milandirect_Review
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2016 Balance
 */
class Milandirect_Review_Block_Override_Product_View_List extends  MageWorx_SeoSuite_Block_Review_Product_View_List
{
    /**
     * Get Product Id
     *
     * @return mixed
     */
    public function getProductId()
    {

        if (Mage::registry('product')) {
            return Mage::registry('product')->getId();
        } else {
            $request = Mage::app()->getRequest()->getParam('list-review-content');
            $productId = $request['params']['id'];
            return $productId ;
        }
    }
}
