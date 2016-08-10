<?php

class Magestore_Megamenu_Adminhtml_Megamenuadmin_TemplateController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction(){
            $this->loadLayout()
                    ->_setActiveMenu('megamenu/template')
                    ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            return $this;
    }

    public function indexAction(){
			if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
            $this->_initAction()
                    ->renderLayout();
    }
    
    public function editAction() {
			if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
            $id	 = $this->getRequest()->getParam('id');
            $model  = Mage::getModel('megamenu/template')->load($id);
            if ($model->getId() || $id == 0) {
                    $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
                    if (!empty($data))
                            $model->setData($data);

                    Mage::register('template_data', $model);

                    $this->loadLayout();
                    $this->_setActiveMenu('megamenu/template');

                    $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
                    $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

                    $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
                    $this->_addContent($this->getLayout()->createBlock('megamenu/adminhtml_template_edit'))
                            ->_addLeft($this->getLayout()->createBlock('megamenu/adminhtml_template_edit_tabs'));

                    $this->renderLayout();
            } else {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('megamenu')->__('Item does not exist'));
                    $this->_redirect('*/*/');
            }
    }

    public function newAction() {
            $this->_forward('edit');
    }
    
    public function deleteAction() {
            if( $this->getRequest()->getParam('id') > 0 ) {
                try {
                    $model = Mage::getModel('megamenu/template');
                    $model->setId($this->getRequest()->getParam('id'))
                            ->delete();
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Template was successfully deleted'));
                    $this->_redirect('*/*/');
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                }
            }
            $this->_redirect('*/*/');
    }
    
    public function saveAction() {
            if ($data = $this->getRequest()->getPost()) {
               //
                $id = $this->getRequest()->getParam('id');
                $model = Mage::getModel('megamenu/template');                 
                if($_FILES['image']['name'] != '' && isset($_FILES['image']['name'])){                     
                    $data['image'] = $_FILES['image']['name'];  
                    $model->setData($data)
                                ->setId($id);
                 }                 
                 else{  
                     
                    $model->setData('name_template', $data['name_template'])->setId($id);
                    $model->setData('code_template', $data['code_template'])->setId($id);
                    $model->setData('description', $data['description'])->setId($id);
                 }
                 if ($data['image']['delete'] == 1){
                    $data['image'] = null;
                    $model->setData('image', $_FILES['image']['name']);
                 }
                try 
                 {
                    if ($model->getCreatedTime() == NULL || $model->getUpdateTime() == NULL)
                            $model->setCreatedTime(now())
                                    ->setUpdateTime(now());
                    else
                            $model->setUpdateTime(now());				
                    $model->save();                               
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('megamenu')->__('Template item was successfully saved'));
                    Mage::getSingleton('adminhtml/session')->setFormData(false);
                    if($data['image'] != '' && isset($data['image'])){
                        if ($this->getRequest()->getParam('id') == NULL){
                            Mage::helper('megamenu')->createImage($data['image'], $model->getCollection()->getLastItem()->getId());
                        }
                        else {
                            Mage::helper('megamenu')->createImage($data['image'], $id);
                        }
                    }                            
                    if ($this->getRequest()->getParam('back')) {
                            $this->_redirect('*/*/edit', array('id' => $model->getId()));
                            return;
                    }

                    $this->_redirect('*/*/');
                    return;
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    Mage::getSingleton('adminhtml/session')->setFormData($data);
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                    return;
                }
            }
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('megamenu')->__('Unable to find Template to save'));
            $this->_redirect('*/*/');
    }
    
    public function massDeleteAction() {
            $megamenuIds = $this->getRequest()->getParam('template');
            if(!is_array($megamenuIds)){
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
            }else{
                    try {
                            foreach ($megamenuIds as $megamenuId) {
                                    $megamenu = Mage::getModel('megamenu/template')->load($megamenuId);
                                    $megamenu->delete();
                            }
                            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($megamenuIds)));
                    } catch (Exception $e) {
                            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    }
            }
            $this->_redirect('*/*/index');
    }
    
    public function exportCsvAction(){
            $fileName   = 'template.csv';
            $content	= $this->getLayout()->createBlock('megamenu/adminhtml_template_grid')->getCsv();
            $this->_prepareDownloadResponse($fileName,$content);
    }

    public function exportXmlAction(){
            $fileName   = 'template.xml';
            $content	= $this->getLayout()->createBlock('megamenu/adminhtml_template_grid')->getXml();
            $this->_prepareDownloadResponse($fileName,$content);
    }

}