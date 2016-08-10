<?php

class Innobyte_ProductQuestions_Adminhtml_Innobyteproductquestions_QuestionController 
    extends Mage_Adminhtml_Controller_Action 
{

    protected function _initAction() 
    {
        $this->loadLayout()->_setActiveMenu('catalog');
        
        $questionId = $this->getRequest()->getParam('id');
        if ($questionId) {
            $model = Mage::getModel('innobyte_product_questions/question')
                    ->load($questionId);
            if ($model->getId()) {
                Mage::register('current_question', $model);
            }
        }
        return $this;
    }

    public function indexAction() 
    {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('innobyte_product_questions/adminhtml_question'));
        $this->renderLayout();
    }
    
    public function gridAction()
    {
        Mage::register('inno_IsProductTabView', true);
        
        $this->_initAction();
        
        $questions = $this->getLayout()
                ->createBlock('innobyte_product_questions/adminhtml_question_grid');
        $this->_addContent($questions);
        $this->getLayout()
                ->getBlock('root')
                ->setTemplate('innobyte/product_questions/product_tab.phtml');
        
        $this->renderLayout();
    }

    public function editAction() 
    {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('innobyte_product_questions/adminhtml_question_edit'));
        
        // Restore form data in case of any errors
        $session = Mage::getSingleton('adminhtml/session');
        Mage::register('question_form_data', $session->getQuestionFormData());
        $session->unsQuestionFormData();
        
        $this->renderLayout();
    }

    public function newAction() 
    {
        $this->_forward('edit');
    }

    public function saveAction() 
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $model = Mage::getModel('innobyte_product_questions/question');
            $helper = Mage::helper('innobyte_product_questions');
            $redirectBack   = $this->getRequest()->getParam('back', false);

            try {
                if (!empty($data['id'])) {
                    $model->load($data['id']);
                }
                Mage::register('program_data', $model);
                $data['created'] = Varien_Date::now();
                if (empty($data['id'])) {
                    $data['store_id'] = Mage::app()->getStore()->getId();
                }
                $model->addData($data);
                $model->save();
                
                if ($this->getRequest()->getParam('reply')) {
                    $helper->sendEmailToCustomer($model);
                }

                Mage::getSingleton('adminhtml/session')
                        ->addSuccess($this->__('Question saved.'));
                
                if ($redirectBack) {
                    $this->_redirect('*/*/edit', array('attribute_id' => $model->getId(),'_current'=>true));
                } else {
                    return $this->_redirect('*/*/index');
                }
            } catch (Exception $e) {
                $session = Mage::getSingleton('adminhtml/session');
                $session->setQuestionFormData($data);
                $session->addError($e->getMessage());
            }
        }

        return $this->_redirectReferer();
    }

    public function deleteAction() 
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('innobyte_product_questions/question');

                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();

                Mage::getSingleton('adminhtml/session')
                        ->addSuccess(Mage::helper('adminhtml')->__('Question was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')
                        ->addError($e->getMessage());
                $this->_redirect('*/*/edit', array(
                    'id' => $this->getRequest()->getParam('id')
                    ));
            }
        }
        $this->_redirect('*/*/');
    }
    /**
     * Acl checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/inno_product_question/product_question');
    }
}
