<?php

class Inecom_Sap_Adminhtml_Sap_OrderController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('system/inecom_sap/order')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Orders rejected by SAP web service'), Mage::helper('adminhtml')->__('Orders rejected by SAP web service'));
		return $this;
	}

	public function indexAction()
    {
        Mage::getSingleton('core/session')->addNotice('This grid is limited to displaying orders that were queued in the last 3 months.');
		$this->_initAction()
			->renderLayout();
	}

	public function showAction()
    {
		$id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('sap/queue')->load($id);

        if ($model->getId() || $id == 0) {

			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);

			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('sap_order_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('system/inecom_sap/order');

			//$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			//$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('sap/adminhtml_order_edit'))
				->_addLeft($this->getLayout()->createBlock('sap/adminhtml_order_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('inecom_sap')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}

    public function queueAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('sap/queue')->load($id);

        $order = Mage::getModel('sales/order')->load($model->getOrderId());

        $model
            ->setData(array(
                'status' => Inecom_Sap_Helper_Order::PENDING
            ))
            ->setId($id)
            ->save();

        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Order %d was placed back into the queue.', $order->getIncrementId()));
        $this->_redirect('*/*/');
    }

	public function deleteAction()
    {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('sap/queue');

				$model->setId($this->getRequest()->getParam('id'))
					->delete();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted from the queue'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction()
    {
        $queueIds = $this->getRequest()->getParam('queue');

        if(!is_array($queueIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($queueIds as $queueId) {
                    $model = Mage::getModel('sap/queue')->load($queueId);
                    $model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($queueIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction()
    {
        $queueIds = $this->getRequest()->getParam('queue');

        if(!is_array($queueIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($queueIds as $queueId) {
                    Mage::getSingleton('sap/queue')
                        ->load($queueId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($queueIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction()
{
        $fileName   = 'test.csv';
        $content    = $this->getLayout()->createBlock('inecom_sap/adminhtml_order_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
}

public function exportXmlAction()
{
        $fileName   = 'test.xml';
        $content    = $this->getLayout()->createBlock('inecom_sap/adminhtml_order_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
}

protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
{
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
}
}