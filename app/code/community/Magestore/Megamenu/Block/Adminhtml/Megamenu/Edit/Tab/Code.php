<?php

class Magestore_Megamenu_Block_Adminhtml_Megamenu_Edit_Tab_Code extends Mage_Adminhtml_Block_Widget_Form
{
	public function __construct()
	{
		parent::__construct();
		//$this->setTemplate('megamenu/code.phtml');
	}
	
	 protected function _prepareLayout()
    {     
        $this->setChild('load_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(
                    array(
                        'label'   => Mage::helper('adminhtml')->__('Load Template'),
                        'onclick' => 'templateControl.load();',
                        'type'    => 'button',
                        'class'   => 'save'
                    )
                )
        );
		
		return parent::_prepareLayout();
	}
	
	public function getLoadButtonHtml()
    {
        return $this->getChildHtml('load_button');
    }
	
	 public function getLoadUrl(){        
        return $this->getUrl('*/*/gettemplate');
    }
    
    public function getSaveUrl(){        
        return $this->getUrl('*/*/save');
    }
	
	protected function _prepareForm(){

            $form = new Varien_Data_Form();
            $this->setForm($form);
            //$form123 = new Varien_Data_Form();
            $data = array();
            if (Mage::getSingleton('adminhtml/session')->getTemplateData())
                    $data = Mage::getSingleton('adminhtml/session')->getTemplateData();
            elseif (Mage::registry('template_data'))
                    $data = Mage::registry('template_data')->getData();

            //$fieldset123 = $form->addFieldset('description_fieldset123', array('legend'=>Mage::helper('megamenu')->__('Load Template')));
            $fieldset = $form->addFieldset('description_fieldset', array('legend'=>Mage::helper('megamenu')->__('Template Information')));                                
            $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
            $wysiwygConfig->addData(array(
                    'add_variables'		=> false,
                    'plugins'			=> array(),
                    'widget_window_url'	=> Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/widget/index'),
                    'directives_url'	=> Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive'),
                    'directives_url_quoted'	=> preg_quote(Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive')),
                    'files_browser_window_url'	=> Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index'),
            ));

			$fieldset->addField('template_select', 'select', array(
                'label'		=> Mage::helper('megamenu')->__('Code Template'),
                'name'		=> 'code',
				//'id'		=> 'template_select',
                'values'	=> Mage::getSingleton('megamenu/megamenu')->getOptionHash(),
				'after_element_html'=> '<div class="value" style="margin-top:10px">'
                            .$this->getLoadButtonHtml().
                        '</div>
						 <div id="advice-validate-select-template_select" class="validation-advice" style="display: none">Please select an option.</div>
						 <div><a class=""><img id="image_template" src =""/></a></div>                                                
						 <script type="text/javascript">
							var templateControl = {
								load: function() {
									if ($(\'template_select\').value == -1){
										$(\'advice-validate-select-template_select\').setStyle({display: \'block\'});
										return
									}else{
										 $(\'advice-validate-select-template_select\').setStyle({display: \'none\'});
									}     
									this.variables = null;
									new Ajax.Request("'.$this->getLoadUrl().'", {
									   parameters: $(\'template_select\').serialize(true),
									   onComplete: function (transport) {
										   if (transport.responseText.isJSON()) {
											   var field = transport.responseText.evalJSON(); 
											   if (field.image){
												   var url = "'.Mage::helper('megamenu')->getUrlImage().'";
												   $(\'image_template\').show();
												   $(\'image_template\').setAttribute(\'src\',url+field.template_id+"/"+field.image);
											   } else {
												   $(\'image_template\').setAttribute(\'src\',\'\');
												   $(\'mage_template\').hide();
											   }
												if (tinyMCE.getInstanceById(\'code_template\') == null){
												   $(\'code_template\').value = field.code_template; 
											   } else {
										tinyMCE.execCommand(\'mceRemoveControl\', false, \'code_template\');
													$(\'code_template\').value = field.code_template; 
													 tinyMCE.execCommand(\'mceAddControl\', false, \'code_template\'); 							
											   }                   
										   }
									   }.bind(this)
									});
								}   
							 }
							//]]>
							</script>
			'));
			
            $fieldset->addField('code_template', 'editor', array(
                    'name'		=> 'code_template',
                    'label'		=> Mage::helper('megamenu')->__('Template Content'),
                    'title'		=> Mage::helper('megamenu')->__('Template Content'),
                    'wysiwyg'	=> true,
                    'required'	=> true,
                    'style'		=> 'width: 600px;',
                    'config'	=> $wysiwygConfig,
            ));
            if(isset($data['image'])){
                $data['image'] = 'megamenu/image/'.$this->getRequest()->getParam('id').'/'.$data['image'];
            }            
            $fieldset->addField('image', 'image', array(			
                    'name'		=> 'image',
                    'label'		=> Mage::helper('megamenu')->__('Up Image Template'),
                    'title'		=> Mage::helper('megamenu')->__('Up Image Template'),                    
            ));
            $form->setValues($data);
            // $form123->setValues($data);
            return parent::_prepareForm();
    }
}