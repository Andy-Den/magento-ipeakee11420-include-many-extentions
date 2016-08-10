<?php
class Balance_Head_Block_Page_Html_Head extends Mage_Page_Block_Html_Head
{
    public function getRobots()
    {
        if ($category = Mage::registry('current_category')) 
        {
            $category = Mage::getModel('catalog/category')->load($category->getId());
            if ($category) {
                $robot_category_value = $category->getData('meta_robots');
                $this->_data['robots'] = $this->getRobotText($robot_category_value);
                return $this->_data['robots'];
            }else{
                return parent::getRobots();
            }
            
        }else if ($_product = Mage::registry('current_product')){
            $product = Mage::getModel("catalog/product")->load($_product->getId());
            if ($product) {
                $categoryIds = $product->getCategoryIds();
                if (is_array($categoryIds) and count($categoryIds) > 1) {
                    $category = Mage::getModel('catalog/category')->load($categoryIds[0]);
                    $robot_category_value = $category->getData('meta_robots');
                    $this->_data['robots'] = $this->getRobotText($robot_category_value);
                    return $this->_data['robots'];
                }else{
                    return parent::getRobots();
                }
            }else{
                return parent::getRobots();
            }
        }else {
            return parent::getRobots();
        }
    }
    function getRobotText($robotId){
        switch ($robotId) {
            case 1:
                $robot_name = "INDEX, FOLLOW";
                break;
            case 2:
                $robot_name = "NOINDEX, FOLLOW";
                break;
            case 3:
                $robot_name = "INDEX, NOFOLLOW";
                break;
            case 4:
                $robot_name = "NOINDEX, NOFOLLOW";
                break;

            default:
                
                $robot_name = Mage::getStoreConfig('design/head/default_robots');
                break;
        }
        
        return $robot_name;
    }
    
}
			