<?php

class Magestore_Megamenu_Block_Adminhtml_Megamenu_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct(){
		parent::__construct();
		
		$this->_objectId = 'id';
		$this->_blockGroup = 'megamenu';
		$this->_controller = 'adminhtml_megamenu';
		
		$this->_updateButton('save', 'label', Mage::helper('megamenu')->__('Save Menu'));
		$this->_updateButton('delete', 'label', Mage::helper('megamenu')->__('Delete Menu'));
		
		$this->_addButton('saveandcontinue', array(
			'label'		=> Mage::helper('adminhtml')->__('Save And Continue Edit'),
			'onclick'	=> 'saveAndContinueEdit()',
			'class'		=> 'save',
		), -100);

		$this->_formScripts[] = '
			function saveAndContinueEdit(){
				editForm.submit($("edit_form").action+"back/edit/");
			}
                        var count = 1;
                    function toggleFeaturedCategories(check){
                        count = count + 1;
                        if($("featured_categories_select").style.display == "none" || (check ==1) || (check == 2)){
                            $("featured_categories_check").style.display ="";
                            var url = "' . $this->getUrl('adminhtml/megamenuadmin_megamenu/chooserCategories') . '";
                            if(check == 1){
                                $("featured_categories").value = $("category_all_ids").value;
                            }else if(check == 2){
                                $("featured_categories").value = "";
                            }
                            var params = $("featured_categories").value.split(", ");
                            var parameters = {"form_key": FORM_KEY,"selected[]":params };
                            var request = new Ajax.Request(url,
                                {
                                    evalScripts: true,
                                    parameters: parameters,
                                    onComplete:function(transport){
                                        $("featured_categories_select").update(transport.responseText);
                                        $("featured_categories_select").style.display = "block"; 
                                    }
                                }
                            );
                        }else{
                              $("featured_categories_select").style.display = "none";
                              $("featured_categories_check").style.display ="none";
                              
                              $("featured_categories_check").style.display ="none";
                        }
                    };
                    function toggleMenuType(){
                        if($("menu_type").value == "6"){
                            $("category_type").up(1).hide();
                            $("category_show_type").up(1).hide();
                            $("colum").up(1).hide();
                            $("categories_box_title").up(1).hide();
                            $("products_box_title").up(1).hide();
                            $("main_content").up(1).hide();
                            $("categories").up(1).hide();
                            $("products").up(1).hide();
                            $("megamenu_featuredcategories").up(".entry-edit").hide();
                            $("megamenu_content_headerfooter").up(".entry-edit").hide();
                            $("megamenu_submenu").hide();
                            $("megamenu_submenu").up().down(".entry-edit-head").hide();
                            $("megamenu_tabs_content_section_content").show();
                        }else if($("menu_type").value == "1"){
                            $("category_type").up(1).hide();
                            $("category_show_type").up(1).hide();
                            $("colum").up(1).hide();
                            $("categories_box_title").up(1).hide();
                            $("products_box_title").up(1).hide();
                            $("main_content").up(1).show();
                            $("categories").up(1).hide();
                            $("products").up(1).hide();
                            $("megamenu_featuredcategories").up(".entry-edit").show();
                            $("megamenu_content_headerfooter").up(".entry-edit").show();
                            $("megamenu_submenu").show();
                            $("megamenu_submenu").up().down(".entry-edit-head").show();
                        }else if($("menu_type").value == "2" || $("menu_type").value == "7"){
                            $("category_type").up(1).hide();
                            $("category_show_type").up(1).hide();
                            $("colum").up(1).show();
                            $("categories_box_title").up(1).hide();
                            $("products_box_title").up(1).show();
                            $("main_content").up(1).hide();
                            $("categories").up(1).hide();
                            $("products").up(1).show();
                            $("megamenu_featuredcategories").up(".entry-edit").show();
                            $("megamenu_content_headerfooter").up(".entry-edit").show();
                            $("megamenu_submenu").show();
                            $("megamenu_submenu").up().down(".entry-edit-head").show();
                        }else if($("menu_type").value == "3"){
                            $("category_type").up(1).show();
                            $("category_show_type").up(1).show();
                            $("colum").up(1).show();
                            $("categories_box_title").up(1).show();
                            $("products_box_title").up(1).hide();
                            $("main_content").up(1).hide();
                            $("categories").up(1).show();
                            $("products").up(1).hide();
                            $("megamenu_featuredcategories").up(".entry-edit").show();
                            $("megamenu_content_headerfooter").up(".entry-edit").show();
                            $("megamenu_submenu").show();
                            $("megamenu_submenu").up().down(".entry-edit-head").show();
                        }else if($("menu_type").value == "4"){
                            $("category_type").up(1).hide();
                            $("category_show_type").up(1).hide();
                            $("colum").up(1).hide();
                            $("categories_box_title").up(1).hide();
                            $("products_box_title").up(1).hide();
                            $("main_content").up(1).hide();
                            $("categories").up(1).hide();
                            $("products").up(1).hide();
                            $("megamenu_featuredcategories").up(".entry-edit").show();
                            $("megamenu_content_headerfooter").up(".entry-edit").show();
                            $("megamenu_submenu").show();
                            $("megamenu_submenu").up().down(".entry-edit-head").show();
                        }
                    }
                    function changeMegamenuType(){
                        if($("megamenu_type").value=="1"){
                            $("submenu_align").up(1).hide();
                            $("leftsubmenu_align").up(1).show();
                        }else{
                            $("submenu_align").up(1).show();
                            $("leftsubmenu_align").up(1).hide();
                        }
                    }
                    function showbylevel(){
                        if($("category_show_type").value == 0  && $("menu_type").value == 3){
                          $("category_type").up(1).show();
                         }else {
                             $("category_type").up(1).hide();
                         } 
                     }
                    Event.observe(window, "load", function(){changeMegamenuType(); toggleMenuType();showbylevel();});
		';
	}

	public function getHeaderText(){
		if(Mage::registry('megamenu_data') && Mage::registry('megamenu_data')->getId())
			return Mage::helper('megamenu')->__("Edit Menu '%s'", $this->htmlEscape(Mage::registry('megamenu_data')->getNameMenu()));
		return Mage::helper('megamenu')->__('Add Menu Item');
	}
}