<?php

class Magestore_Megamenu_Block_Adminhtml_Megamenu_Edit_Tab_Content_Featureditem extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        if (Mage::getSingleton('adminhtml/session')->getMegamenuData()) {
            $data = Mage::getSingleton('adminhtml/session')->getMegamenuData();
            Mage::getSingleton('adminhtml/session')->setMegamenuData(null);
        } elseif (Mage::registry('megamenu_data'))
            $data = Mage::registry('megamenu_data')->getData();

        $fieldset = $form->addFieldSet('megamenu_featuredcategories', array('legend' => Mage::helper('megamenu')->__('Featured Content')));
        $categoryBlock = $this->getLayout()->createBlock(
                        'megamenu/adminhtml_megamenu_edit_tab_content_featureditem_categories', 'featured_category', array('js_form_object' => 'megamenu_featuredcategories')
                )
                ->setCategoryIds(array());
        $productBlock = $this->getLayout()->createBlock(
                        'megamenu/adminhtml_megamenu_edit_tab_content_featureditem_products', 'featured_product', array('js_form_object' => 'megamenu_featuredcategories')
                )
                ->setProductIds(array());
        $categoryIds = implode(", ", Mage::getResourceModel('catalog/category_collection')->addFieldToFilter('level', array('gt' => 0))->getAllIds());
        $fieldset->addField('featured_type', 'select', array(
            'label' => Mage::helper('megamenu')->__('Featured Type'),
            'name' => 'featured_type',
            'onchange' => 'changeType()',
            'values' => Mage::helper('megamenu')->getFeaturedTypes(),
            'after_element_html' => '
                <input type="hidden" value="' . $categoryIds . '" id="category_all_ids" />'
                )
        );
        $type = 0;
        if (isset($data['featured_type']) && $data['featured_type']) {
            $type = $data['featured_type'];
        }
        
        if(!isset($data['featured_width']))
            $data['featured_width'] = 30;
        $fieldset->addField('featured_width', 'note', array(
            'label'        => Mage::helper('megamenu')->__('Featured Width'),
            'name'        => 'featured_width',
            'text' =>$data['featured_width'].'%',
            'after_element_html' => '</br><input type="range" onchange="$(\'featured_width\').update(this.value+\'%\')" id="featured_width_slide" name="featured_width" value="'.$data['featured_width'].'">',
            'note' => Mage::helper('megamenu')->__('Percentage of Featured content in Menu content')
        ));
        
        $fieldset->addField('featured_categories_box_title', 'text', array(
            'label' => Mage::helper('megamenu')->__('Featured Box Title'),
            'index' => 'featured_categories_box_title',
            'name' => 'featured_categories_box_title'
        ));
        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
			array(
				'hidden'=>false,
				'add_variables' => true, 
				'add_widgets' => true,
				'add_images'=>true,
				'widget_window_url'	=> Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/widget/index'),
				'directives_url'	=> Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive'),
				'directives_url_quoted'	=> preg_quote(Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive')),
				'files_browser_window_url'	=> Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index')
			)
		);
        $fieldset->addField('featured_content', 'editor', array(
            'label' => Mage::helper('megamenu')->__('Featured Content'),
            'title' => Mage::helper('megamenu')->__('Featured Content'),
            'name' => 'featured_content',
            'wysiwyg' => true,
            'config'        =>$wysiwygConfig,
        ));
        
        $fieldset->addField('featured_products_box_title', 'text', array(
            'label' => Mage::helper('megamenu')->__('Featured Box Title'),
            'index' => 'featured_products_box_title',
            'name' => 'featured_products_box_title'
        ));
        if(!isset($data['featured_column']) || !$data['featured_column'])
            $data['featured_column'] = 2;
         $fieldset->addField('featured_column', 'text', array(
            'label' => Mage::helper('megamenu')->__('Featured Column Number'),
            'index' => 'featured_column',
            'name' => 'featured_column',
            'note' => '
              <span><a href="JavaScript:void(0);" id="featured_number_tooltip">'.$this->__('What is this?').'</a> <br>'.$this->__('If the value is zero or empty, default will be 2').      
                  '<script type="text/javascript">
                      tips = new Tooltip("featured_number_tooltip","'.Mage::getBaseUrl('media').'megamenu/featured.png");
                  </script>
              </span>'
        ));
        $fieldset->addField('featured_categories', 'text', array(
            'label' => Mage::helper('megamenu')->__('Featured Categories'),
            'name' => 'featured_categories',
            'disabled' => 'disabled',
            'after_element_html' => '<a id="category_link" href="javascript:void(0)" onclick="toggleFeaturedCategories()"><img src="' . $this->getSkinUrl('images/rule_chooser_trigger.gif') . '" alt="" class="v-middle rule-chooser-trigger" title="Select Categories"></a>
            <div  id="featured_categories_check" style="display:none">
                <a href="javascript:toggleFeaturedCategories(1)">Check All</a> / <a href="javascript:toggleFeaturedCategories(2)">Uncheck All</a>
            </div>
            <div id="featured_categories_select" style="display:none">
            </div>
                ',
            'note' =>  Mage::helper('megamenu')->__('Upload images of categories selected for the best result.')
        ));
        $productIds = implode(", ", Mage::getResourceModel('catalog/product_collection')->getAllIds());
        $fieldset->addField('featured_products', 'text', array(
            'label' => Mage::helper('megamenu')->__('Featured Products'),
            'name' => 'featured_products',
            'class' => 'rule-param',
            'disabled' => 'disabled',
            'after_element_html' => '<a id="item_product_link" href="javascript:void(0)" onclick="toggleFeaturedProducts()"><img src="' . $this->getSkinUrl('images/rule_chooser_trigger.gif') . '" alt="" class="v-middle rule-chooser-trigger" title="Select Products"></a><input type="hidden" value="' . $productIds . '" id="item_product_all_ids"/><div id="featured_products_select" style="display:none;width:640px"></div>
		<script type="text/javascript">
                    function toggleFeaturedProducts(){
                        if($("featured_products_select").style.display == "none"){
                        var url = "' . $this->getUrl('adminhtml/megamenuadmin_megamenu/chooserFeaturedProducts') . '";
                        var params = $("featured_products").value.split(", ");
                        var parameters = {"form_key": FORM_KEY,"selected[]":params };
                        var request = new Ajax.Request(url,
                            {
                                evalScripts: true,
                                parameters: parameters,
                                onComplete:function(transport){
                                    $("featured_products_select").update(transport.responseText);
                                    $("featured_products_select").style.display = "block"; 
                                }
                            });
                            }else{
                                $("featured_products_select").style.display = "none";
                            }
                    };
                     var featured_grid;
                    function constructFeaturedData(div){
                        featured_grid = window[div.id+"JsObject"];
                        if(!featured_grid.reloadParams){
                            featured_grid.reloadParams = {};
                            featured_grid.reloadParams["selected[]"] = $("featured_products").value.split(", ");
                        }
                    }
                    function selectFeaturedProduct(e) {
                        if(e.checked == true){
                            if(e.id == "featured_on"){
                                $("featured_products").value = $("item_product_all_ids").value;
                            }else{
                                if($("featured_products").value == "")
                                    $("featured_products").value = e.value;
                                else
                                    $("featured_products").value = $("featured_products").value + ", "+e.value;
                            }
                            featured_grid.reloadParams["selected[]"] = $("featured_products").value.split(", ");
                        }else{
                             if(e.id == "featured_on"){
                                $("featured_products").value = "";
                            }else{
                                var vl = e.value;
                                if($("featured_products").value.search(vl) == 0){
                                    $("featured_products").value = $("featured_products").value.replace(vl+", ","");
                                }else{
                                    $("featured_products").value = $("featured_products").value.replace(", "+ vl,"");
                                }
                            }
                        }
                    }
                    changeType();
                    function changeType(){
                        if($("featured_type").value == "3"){
                            $("megamenu_featuredcategories").down("#featured_column").disabled = false;
                            $("megamenu_featuredcategories").down("#featured_column").up().up().style.display = "";
                            $("megamenu_featuredcategories").down("#featured_width").up().up().style.display = "";
                            $("megamenu_featuredcategories").down("#featured_categories").disabled = true;
                            $("megamenu_featuredcategories").down("#featured_products").disabled = true;
                            $("megamenu_featuredcategories").down("#featured_products").up().up().style.display = "none";
                            $("megamenu_featuredcategories").down("#featured_categories").up().up().style.display = "none";
                            $("megamenu_featuredcategories").down("#featured_content").up().up().style.display = "";
                            $("megamenu_featuredcategories").down("#featured_products_box_title").up().up().style.display = "none";
                            $("megamenu_featuredcategories").down("#featured_categories_box_title").up().up().style.display = "none";
                            $("megamenu_featuredcategories").down("#featured_column").up().up().style.display = "none";
                            
                            $("megamenu_featuredcategories").down("#category_link").style.display = "none";
                            $("megamenu_featuredcategories").down("#item_product_link").style.display = "none";
                        }else if($("featured_type").value == "2"){
                            $("megamenu_featuredcategories").down("#featured_column").disabled = false;
                            $("megamenu_featuredcategories").down("#featured_column").up().up().style.display = "";
                            $("megamenu_featuredcategories").down("#featured_content").up().up().style.display = "none";
                            $("megamenu_featuredcategories").down("#featured_width").up().up().style.display = "";
                            $("megamenu_featuredcategories").down("#featured_categories").disabled = false;
                            $("megamenu_featuredcategories").down("#featured_products").disabled = true;
                            $("megamenu_featuredcategories").down("#featured_products").up().up().style.display = "none";
                            $("megamenu_featuredcategories").down("#featured_categories").up().up().style.display = "";
                            $("megamenu_featuredcategories").down("#featured_column").up().up().style.display = "";
                            
                            $("megamenu_featuredcategories").down("#featured_products_box_title").up().up().style.display = "none";
                            $("megamenu_featuredcategories").down("#featured_categories_box_title").up().up().style.display = "";
                            
                            $("megamenu_featuredcategories").down("#category_link").style.display = "";
                            $("megamenu_featuredcategories").down("#item_product_link").style.display = "none";
                        }else if($("featured_type").value == "1"){
                            $("megamenu_featuredcategories").down("#featured_column").disabled = false;
                            $("megamenu_featuredcategories").down("#featured_column").up().up().style.display = "";
                            $("megamenu_featuredcategories").down("#featured_content").up().up().style.display = "none";
                            $("megamenu_featuredcategories").down("#featured_width").up().up().style.display = "";
                            $("megamenu_featuredcategories").down("#featured_products").disabled = false;
                            $("megamenu_featuredcategories").down("#featured_categories").disabled = true;
                            $("megamenu_featuredcategories").down("#featured_categories").up().up().style.display = "none";
                            $("megamenu_featuredcategories").down("#featured_products").up().up().style.display = "";
                            $("megamenu_featuredcategories").down("#featured_column").up().up().style.display = "";
                            
                            $("megamenu_featuredcategories").down("#featured_categories_box_title").up().up().style.display = "none";
                            $("megamenu_featuredcategories").down("#featured_products_box_title").up().up().style.display = "";

                            $("megamenu_featuredcategories").down("#category_link").style.display = "none";
                            $("megamenu_featuredcategories").down("#item_product_link").style.display = "";
                        }else{
                            
                            $("megamenu_featuredcategories").down("#featured_column").disabled = true;
                            $("megamenu_featuredcategories").down("#featured_column").up().up().style.display = "none";
                            $("megamenu_featuredcategories").down("#featured_content").up().up().style.display = "none";
                            $("megamenu_featuredcategories").down("#featured_categories").disabled = true;
                            $("megamenu_featuredcategories").down("#featured_products").disabled = true;
                            $("megamenu_featuredcategories").down("#featured_width").up().up().style.display = "none";
                            $("megamenu_featuredcategories").down("#featured_products").up().up().style.display = "none";
                            $("megamenu_featuredcategories").down("#featured_categories").up().up().style.display = "none";
                            
                            $("megamenu_featuredcategories").down("#featured_products_box_title").up().up().style.display = "none";
                            $("megamenu_featuredcategories").down("#featured_categories_box_title").up().up().style.display = "none";
                            
                            $("megamenu_featuredcategories").down("#category_link").style.display = "none";
                            $("megamenu_featuredcategories").down("#item_product_link").style.display = "none";
                        }
                    }
                </script>'
        ));

        $form->setValues($data);
        return parent::_prepareForm();
    }

}