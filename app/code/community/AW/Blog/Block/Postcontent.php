<?php

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-ENTERPRISE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento ENTERPRISE edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento ENTERPRISE edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Blog
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-ENTERPRISE.txt
 */
class AW_Blog_Block_Postcontent extends AW_Blog_Block_Abstract {

    public $_postId=0;

    public function setPostId($id){
        $this->_postId = $id;
        return $this;
    }

    public function  getPostId(){
        return $this->_postId;
    }

    public function getPost(){
        if($this->getPostId()==0){
            return null;
        }
        else{
            $postId =$this->getPostId();
            $post = Mage::getModel('blog/post')->load($postId);
            return $post;
        }
    }
}
