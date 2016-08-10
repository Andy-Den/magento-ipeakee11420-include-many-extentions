<?php

class Balance_Exclusion_Adminhtml_Exclusion_ExclusionController extends Mage_Adminhtml_Controller_action
{

    private $xml;

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('exclusion/items')
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Feeds Manager'),
                Mage::helper('adminhtml')->__('Feeds Manager')
            );

        return $this;
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('exclusion/exclusion')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('exclusion_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('exclusion/items');

            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Feed Manager'),
                Mage::helper('adminhtml')->__('Feed Manager')
            );
            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Feed Managemet'),
                Mage::helper('adminhtml')->__('Feed Managemet')
            );

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);


            $this->_addContent($this->getLayout()->createBlock('exclusion/adminhtml_exclusion_edit'))
                ->_addLeft($this->getLayout()->createBlock('exclusion/adminhtml_exclusion_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('exclusion')->__("Feeds don't exist"));
            $this->_redirect('*/*/');
        }
    }

    public function indexAction()
    {
        $this->_title($this->__('Terms'))
            ->_title($this->__('Manage Terms'));

        $this->loadLayout();
        $this->renderLayout();
    }

    public function newAction()
    {


        $this->_forward('edit');
    }

    public function saveAction()
    {
        $data = $this->getRequest()->getPost();
        $model = Mage::getModel('exclusion/exclusion');
        $model->setData($data)
            ->setId($this->getRequest()->getParam('id'));

        try {
            if ($model->getCreatedTime == null || $model->getUpdateTime() == null) {
                $model->setCreatedTime(now())
                    ->setUpdateTime(now());
            } else {
                $model->setUpdateTime(now());
            }

            $model->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('exclusion')->__('Term was successfully saved')
            );
            Mage::getSingleton('adminhtml/session')->setFormData(false);

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

    public function massDeleteAction()
    {

        $ids = $this->getRequest()->getParam('id');

        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('tax')->__('Please select term(s).'));
        } else {
            try {
                $exclusionModel = Mage::getModel('exclusion/exclusion');
                foreach ($ids as $id) {
                    $exclusionModel->load($id)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('tax')->__(
                        'Total of %d record(s) were deleted.',
                        count($ids)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

}