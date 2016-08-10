<?php
/**
 * Guest print order receipt 
 * 
 * As a Guest user print order receipt from checkout success page
 * 
 * @category  Sales
 * @author    Created By : Mage Magician
 * @copyright Mage Magician
 * @version  v 0.1.0  
 * @filesource
 * @link      
 */

require_once("Mage/Sales/controllers/GuestController.php");
 
class Magemagician_Guestorderprint_GuestController extends Mage_Sales_GuestController
{
    
	/**
     * Load valid order and register it
     *
     * @param 	int $orderId
     * @return 	bool
     */
    protected function _loadGuestValidOrder($orderId = null)
    {
        if (null === $orderId) {
            $orderId = (int) $this->getRequest()->getParam('order_id');
			$case = 'order';
			if (!$orderId) {
                            
//				$orderId = base64_decode(Mage::helper('core')->decrypt(str_replace(' ', '+', $this->getRequest()->getParam('incrementorder_id'))));                                                                                          
                                $orderId = base64_decode($this->getRequest()->getParam('incrementorder_id')); 
				$case = 'incrementorder';
			}
        }
		
        if (!$orderId) {
            $this->_redirect('sales/guest/form');
            return false;
        }

        switch ($case) {
			case 'order':
				$order = Mage::getModel('sales/order')->load($orderId);
				break;
			case 'incrementorder':
				$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
				break;
			default:
				$order = Mage::getModel('sales/order')->load($orderId);
				break;
		}
		

        if ($order->getId()) {
            Mage::register('current_order', $order);
            return true;
        } else {
            $this->_redirect('sales/guest/form');
        }
		
        return false;
    }
	
	/**
     * Print Order Action
     */
    public function printAction()
    {
        if (!$this->_loadGuestValidOrder()) {
            return;
        }
        
		$this->loadLayout('print');
        $this->renderLayout();
    }
}
