<?php

class Magestore_Megamenu_Block_Navigationtop extends Mage_Catalog_Block_Navigation
{
    
    /**
     * get list product for menu
     * 
     * @param type $idsu
     * @return Varien_Object 
     */
    public function getListProduct($idsu){
        $ids = array();       
        $ids = explode(",", $idsu);
        $categoryIds = array_filter($ids);        
        if (count($categoryIds)){            
            $productCollection = Mage::getResourceModel('catalog/product_collection'); 
            $productCollection->getSelect()
            ->join(
            array('c' => $productCollection->getTable('catalog/category_product')),
            'e.entity_id = c.product_id',
            array()
            )->where('c.category_id IN ('.implode(',',$categoryIds).')')
            ->group('e.entity_id');                   

            $productCollection->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                     ->addMinimalPrice()
                     ->addFinalPrice()
                     ->addTaxPercents();
            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($productCollection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($productCollection);                
            $productCollection->addUrlRewrite(0);              
            return $productCollection;            
        }      
        return new Varien_Object();
    }
    
    /**
     *
     * @return model megamenu 
     */
    public function getCollection(){
        $collection = Mage::getModel('megamenu/megamenu')
                        ->getCollection()
                        ->addFieldToFilter('status',1)
                        ->setOrder('sort_order','ASC');
        $current_store = Mage::app()->getStore()->getId();
        //$megamenus = array();
        $collection->getSelect()->where('FIND_IN_SET(0, stores) OR FIND_IN_SET(?, stores)', $current_store);       
        return $collection;
    }
    /**
     *
     * @param type $style_show
     * @param type $id_block
     * @param type $id
     * @param type $colum
     * @param type $size_mega
     * @param type $size_colum    
     * @param type $colum_category
     * @param type $size_category     
     */
    public function getShow($style_show,$id, $colum,$size_mega, $size_colum, $colum_category, $size_category, $template, $position, $categories){
        //Show template          
        if ($style_show == 1){            
            if($size_mega != 0){
                $size = $size_mega;                    
            }     
            $block = $this->rendstaticblock($size, $template);             
                return $block;
        }
       //Show Product           
        else if ($style_show == 2){
            if (isset($categories) && $categories){
                $size_product = 0;                
                $list = $this->getListProduct($categories);            
                $p_product = count($list);                    
                if($p_product){
                    if($colum == 1){
                            $size_magrin = 26;
                    }
                    else{
                        $size_magrin = 26*($colum-1);    
                    }                    
                    $size_product = ($size_colum)*$colum+$size_magrin;//var_dump($size_magrin);die();                   
                }               
                if($size_mega == 0){
                $size = $size_product + 10;            
                }
                else{                    
                    $size = $size_mega;                 
                }            
                $block = $this->rend ($colum, $id, $size_colum, 13,$list,$size, $template, $p_product);            
                return $block;
            }
            else{
                return null;
            }
            
        }
       // Show both template and product             
        else if ($style_show == 3){                
            $size_product = 0;
            if (isset ($categories) && $categories){
                $list = $this->getListProduct($categories);            
                $product = count($list);               
                if($product){
                    if($colum == 1){
                            $size_magrin = 26;
                    }
                    else{
                        $size_magrin = 26*($colum-1);    
                    }

                    $size_product = ($size_colum)*$colum+$size_magrin;//var_dump($size_magrin);die();                   
                }
            }            
            if($size_mega == 0){            
                if($size_product != 0){
                    $size = $size_product;
                }
            }
            else{                    
                $size = $size_mega;                
            }            
            $block = $this->rend ($colum, $id, $size_colum, 14,$list, $size, $template, $product);                                             
            return $block;
        }
        // Show categories          
        else if($style_show == 4){            
            $ids = explode(",", $categories);	 
            $categoryIds = array_filter($ids);                
            $block = $this->rendcategory($id,13,$colum_category, $size_category,$categoryIds, $size_mega);
            return $block ;
        }
        else return null;
    }
    
    function rendstaticblock($size, $template){
        $block = $this->getLayout()->createBlock('megamenu/staticblock');
        $block->setTemplate('megamenu/staticblock.phtml');        
        $block->setData('size', $size);
        $block->setData('template', $template);        
        return $block->toHtml();
    }
    
    function rendcategory($id,$level,$colum_category, $size_category,$categoryIds, $size_mega){
        $block = $this->getLayout()->createBlock('megamenu/categories');
        $block->setTemplate('megamenu/categories.phtml');
        $block->setData('id', $id);
        $block->setData('level', $level);
        $block->setData('colum_category', $colum_category);
        $block->setData('size_category', $size_category);
        $block->setData('categoryIds', $categoryIds);
        $block->setData('size_mega', $size_mega);         
        return $block->toHtml();
    }
    
    function rend($colum, $id, $size_colum, $level, $list, $size_megamenu, $template, $size_list){            
        $block = $this->getLayout()->createBlock('megamenu/products');
        $block->setTemplate('megamenu/products.phtml'); 
        $block->setData('size_megamenu', $size_megamenu);
        $block->setData('colum',$colum);
        $block->setData('id', $id);
        $block->setData('size_colum', $size_colum);
        $block->setData('level',$level);
        $block->setData('template', $template);       
        $block->setData('list', $list);         
        $block->setData('size_list', $size_list);         
        return $block->toHtml();           
    }        
	
	public function setTemplate($template)
	{
		if(!Mage::helper('magenotification')->checkLicenseKey('Megamenu')){
			return $this;
		}
		return parent::setTemplate($template);
	}
}

