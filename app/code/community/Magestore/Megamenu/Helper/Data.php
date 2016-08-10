<?php

class Magestore_Megamenu_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getSelectedStateSegment($currentUrl, $baseUrl) {
        $currentUrl = str_replace($baseUrl, '', $currentUrl);
        $currentUrl = str_replace('index.php/', '', $currentUrl);
        $currentUrl = $this->_hasStartingSlash($currentUrl);
        return $this->_removeDotHtml($this->_getSelectedStateSegment($currentUrl));
    }

    private function _getSelectedStateSegment($currentUrl) {
        $explodedCurrentUrl = explode('/', $currentUrl);
        return array_key_exists(0, $explodedCurrentUrl) ? $explodedCurrentUrl[0] : false;
    }

    public function getUrlImageCache() {

        return Mage::getBaseUrl('media') . 'megamenu/image/cache/';
    }

    public function getUrlImage() {
        return Mage::getBaseUrl('media') . 'megamenu/image/';
    }

    public function getUrlImageAdmin($id) {
        $collection = Mage::getModel('megamenu/template')->load($id);
        $image = $collection->getData('image');
        return Mage::getBaseUrl('media') . 'megamenu/image/' . $id . '.' . $image;
    }

    public function createImage($image, $id) {
        if (isset($image) && $image != '') {
            try {
                /* Starting upload */
                $uploader = new Varien_File_Uploader('image');

                // Any extention would work
                $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                $uploader->setAllowRenameFiles(true);
                // Set the file upload mode 
                // false -> get the file directly in the specified folder
                // true -> get the file in the product like folders 
                //	(file.jpg will go in something like /media/f/i/file.jpg)
                $uploader->setFilesDispersion(false);

                // We set media as the upload dir
                $path = Mage::getBaseDir('media') . DS . 'megamenu' . DS . 'image' . DS . $id;
                $uploader->save($path, $image);
                $path_resze = Mage::getBaseDir('media') . DS . 'megamenu' . DS . 'image' . DS . 'cache' . DS . $id . DS . $image;
                $imageObj = new Varien_Image($path . DS . $image);
                $imageObj->constrainOnly(TRUE);
                $imageObj->keepAspectRatio(TRUE);
                $imageObj->keepFrame(FALSE);
                $imageObj->resize(350, 150);
                $imageObj->save($path_resze);
            } catch (Exception $e) {
                
            }
        }
    }

    public function ImportImage($image, $id, $pathimage) {
        $path = Mage::getBaseDir('media') . DS . 'megamenu' . DS . 'image' . DS . $id . DS . $image;
        $imageObjfull = new Varien_Image($pathimage);
        $imageObjfull->constrainOnly(TRUE);
        $imageObjfull->keepAspectRatio(TRUE);
        $imageObjfull->keepFrame(FALSE);
        $imageObjfull->save($path);

        $path_resze = Mage::getBaseDir('media') . DS . 'megamenu' . DS . 'image' . DS . 'cache' . DS . $id . DS . $image;
        $imageObj = new Varien_Image($pathimage);
        $imageObj->constrainOnly(TRUE);
        $imageObj->keepAspectRatio(TRUE);
        $imageObj->keepFrame(FALSE);
        $imageObj->resize(350, 150);
        $imageObj->save($path_resze);
    }

    public function getPathImageImport($name) {
        return Mage::getBaseDir('media') . DS . 'import' . DS . $name;
    }

    public function AdminImage($id) {
        if ($id != NULL) {
            $collection = Mage::getModel('megamenu/template')->load($id);

            $image = $collection->getData('image');

            $url = $this->getUrlImage() . $id . "/" . $image;
            $re = '<a><img id="image_small" src = "' . $url . '" width="30px" height="30px"/></a>
            <script type="text/javascript">                                           
                    tip = new Tooltip("image_small", "' . $url . '");                                                
            </script>
            ';
            return $re;
        } else {
            return "Up image";
        }
    }

    public function returnlayout() {
        return '&nbsp;&nbsp;&lt;default&gt;<br/>              
                &nbsp;&nbsp;&nbsp;&nbsp;&lt;reference name="left&gt;<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;block name="leftmenu" type="megamenu/megamenu" template="megamenu/megamenu-left.phtml" before="-"/&gt<br/>         
                 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/reference&gt<br/>
          &nbsp;&nbsp;&lt;/default&gt';
    }

    public function returnblock() {
        return '&nbsp;&nbsp{{block type="megamenu/megamenu" template="megamenu/megamenu-left.phtml"}}<br>';
    }

    public function getFullProductUrl($pro) {
        return $pro->getProductUrl();
    }

    public function returntext() {
        return 'If you enable this module, it will rewrite your current top menu by a new mega menu. Also, you can show left mega menu in other places by choosing one of the options below (recommended for developers)';
    }

    public function returntemplate() {
        return "&nbsp;&nbsp;\$this->getLayout()->createBlock('megamenu/megamenu')->setTemplate('megamenu/megamenu-left.phtml')<br/>&nbsp;&nbsp;->tohtml();";
    }

    /**
     * get menu type
     * @return menu type array
     */
    public function getMenutypeOptions() {
        return array(
            array(
                'label' => 'Content Only',
                'value' => '1'
            ),
            array(
                'label' => 'Product Listing',
                'value' => '2'
            ),
            array(
                'label' => 'Category Listing',
                'value' => '3'
            ),
            array(
                'label' => 'Group Menu Items',
                'value' => '5'
            ),
            array(
                'label' => 'Contact Form',
                'value' => '4'
            ),
            array(
                'label' => 'Anchor Text',
                'value' => '6'
            ),
             array(
                'label' => 'Product Grid',
                'value' => '7'
            ),
        );
    }
    public function getMegamenutypeOptions() {
        return array(
            array(
                'label' => 'Top Menu',
                'value' => 0
            ),
            array(
                'label' => 'Left Menu',
                'value' => 1
            ),
           
        );
    }
    /**
     * get menu type options for grid menu item
     * @return menu type options
     */
    public function menuTypeToOptionArray() {
        $result = array();
        $array = $this->getMenutypeOptions();
        foreach ($array as $item) {
            $result[$item['value']] = $item['label'];
        }
        return $result;
    }
     public function megamenuTypeToOptionArray() {
        $result = array();
        $array = $this->getMegamenutypeOptions();
        foreach ($array as $item) {
            $result[$item['value']] = $item['label'];
        }
        return $result;
    }

    /**
     * get featured type: none, product, category
     * @return array
     */
    public function getFeaturedTypes() {
        return array(
            array(
                'label' => 'None',
                'value' => '0'
            ),
            array(
                'label' => 'Product',
                'value' => '1'
            ),
            array(
                'label' => 'Category',
                'value' => '2'
            ),
            array(
                'label' => 'Content',
                'value' => '3'
            )
        );
    }

    /**
     * get font style
     * @return font array
     */
    public function getFontStyle() {
        return array(
            array(
                'label' => 'Arial',
                'value' => 'Arial,Helmet,Freesans,sans-serif'
            ),
            array(
                'label' => 'Times New Roman',
                'value' => 'Times New Roman'
            ),
            array(
                'label' => 'Tahoma',
                'value' => 'Tahoma, Geneva, sans-serif'
            ),
            array(
                'label' => 'Verdana',
                'value' => 'Verdana, Geneva, sans-serif'
            ),
            array(
                'label' => 'Georgia',
                'value' => 'Georgia, serif'
            ),
            array(
                'label' => 'Bookman Old Style',
                'value' => 'Bookman Old Style, serif'
            ),
            array(
                'label' => 'Comic Sans MS',
                'value' => 'Comic Sans MS, cursive'
            ),
            array(
                'label' => 'Courier New',
                'value' => 'Courier New, Courier, monospace'
            ),
            array(
                'label' => 'Garamond',
                'value' => 'Garamond, serif'
            ),
            array(
                'label' => 'Georgia',
                'value' => 'Georgia, serif'
            ),
            array(
                'label' => 'Impact, Charcoal',
                'value' => 'Impact, Charcoal, sans-serif'
            ),
            array(
                'label' => 'Lucida Console, Monaco',
                'value' => 'Lucida Console, Monaco, monospace'
            ),
            array(
                'label' => 'Tahoma',
                'value' => 'Tahoma, Geneva, sans-serif'
            ),
            array(
                'label' => 'Webdings',
                'value' => 'Webdings, sans-serif'
            )
        );
    }

    /**
     * Save html into system config
     */
    public function saveCacheHtml2($store = null) {
        Mage::app()->getCacheInstance()->cleanType('config');
        if (!$store) {
            $stores = Mage::app()->getStores(true);
            foreach ($stores as $id => $store) {
                Mage::app()->setCurrentStore($store->getId());
                $block = Mage::app()->getLayout()->createBlock('megamenu/navigationtop')
                        ->setArea('frontend')
                        ->setStore($id)
                        ->setTemplate('megamenu/navigation_top.phtml');
                $html = $block->toHtml();
                Mage::getModel('core/config')->saveConfig('megamenu/general/template', $html, 'stores', $id);
            }
        }

        Mage::getModel('core/config')->saveConfig('megamenu/general/reindex', 0);
        Mage::app()->getCacheInstance()->cleanType('config');
        Mage::app()->getCacheInstance()->cleanType('block_html');
    }

    public function saveCacheHtml($store = null) {
        $currentStore = Mage::app()->getStore()->getStoreId();
        $stores = Mage::app()->getStores(true);
        foreach ($stores as $id => $store) {
            Mage::app()->setCurrentStore($store->getId());
            $block = Mage::app()->getLayout()->createBlock('megamenu/navigationtop')
                    ->setArea('frontend')
                    ->setStore($id)
                    ->setTemplate('megamenu/topmenu.phtml');

            $html = $block->toHtml();
            $staticBlock = Mage::getModel('cms/block')->load('megamenu_' . $id, 'identifier');
            if (!$staticBlock->getId()) {
                $staticBlock = Mage::getModel('cms/block');
                $staticBlock->setData('title', 'Mega Menu ' . $store->getName());
                $staticBlock->setData('identifier', 'megamenu_' . $id);
                $staticBlock->setId(null)->save();
            }
            $staticBlock->setStores(array($id))->setContent($html)->save();
            /* --- Left menu -----*/
            $blockleft = Mage::app()->getLayout()->createBlock('megamenu/navigationleft')
                    ->setArea('frontend')
                    ->setStore($id)
                    ->setTemplate('megamenu/navigationleft.phtml');

            $htmlleft = $blockleft->toHtml();
            $staticBlockleft = Mage::getModel('cms/block')->load('megamenuleft_' . $id, 'identifier');
            if (!$staticBlockleft->getId()) {
                $staticBlockleft = Mage::getModel('cms/block');
                $staticBlockleft->setData('title', 'Mega Menu ' . $store->getName());
                $staticBlockleft->setData('identifier', 'megamenuleft_' . $id);
                $staticBlockleft->setId(null)->save();
            }
            $staticBlockleft->setStores(array($id))->setContent($htmlleft)->save();
            /*---- End ------*/
        }
        Mage::app()->setCurrentStore($currentStore);
        Mage::getModel('core/config')->saveConfig('megamenu/general/reindex', 0);
        Mage::app()->getCacheInstance()->cleanType('config');
        Mage::app()->getCacheInstance()->cleanType('block_html');
    }

    public function positionIsAuto() {
        $store = Mage::app()->getStore()->getId();
        $positionType = Mage::getStoreConfig('megamenu/general/menu_position_type', $store);
        if ($positionType == 1) {
            return true;
        }
        return false;
    }

    public function positionSubAuto($align) {
        switch ($align) {
            case 0:
                $sub_position = 'sub_left';
                break;
            case 1:
                $sub_position = 'sub_right';
                break;
            case 2:
                $sub_position = 'sub_left position_auto';
                break;
            case 3:
                $sub_position = 'sub_right position_auto';
                break;
            default:
                break;
        }
        return $sub_position;
    }
    public function positionLeftSubAuto($align) {
            switch ($align) {
                case 0:
                    $sub_position = 'position_menu';
                    break;
                case 1:
                    $sub_position = 'position_item';
                    break;
                default:
                    break;
            }
            return $sub_position;
        }
    public function getCssgen() {
         $file =  Mage::getBaseDir('skin').'/frontend/base/default/megacssgen/megamenuimport.css';
        $handle = @fopen($file, "w");
         $contents = fread($handle, filesize($filename));
       
        //Zend_debug::dump(file_exists($file));die();
        $content = Mage::app()->getLayout()->createBlock('megamenu/cssgen')
                ->setTemplate('megamenu/cssgen.phtml')
                  ->toHtml();
        //return $content;
         file_put_contents($file, $content);
          fclose($handle);
    }
    public function setLevel($level){
        switch ($level) {
                case 1:
                    $class = 'level1';
                    break;
                case 2:
                    $class = 'level2';
                    break;
                case 3:
                    $class = 'level3';
                    break;
                default:
                    break;
            }
            return $class;
    }

}
