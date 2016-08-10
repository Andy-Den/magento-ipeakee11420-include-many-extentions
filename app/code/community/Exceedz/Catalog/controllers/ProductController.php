<?php
/**
 * Product controller
 *
 * @category   Exceedz
 * @package    Exceedz_Catalog
 */
require_once('Mage/Catalog/controllers/ProductController.php');
class Exceedz_Catalog_ProductController extends Mage_Catalog_ProductController
{
	/**
     * Product view action
     */
    public function showpopupAction()
    {
       if ($product = $this->_initProduct()) {
            Mage::dispatchEvent('catalog_controller_product_view', array('product'=>$product));
            if (!$this->getRequest()->getParam('options')) {
                $notice = $product->getTypeInstance(true)->getSpecifyOptionMessage();
                Mage::getSingleton('catalog/session')->addNotice($notice);
            }
            Mage::getSingleton('catalog/session')->setLastViewedProductId($product->getId());
            Mage::getModel('catalog/design')->applyDesign($product, Mage_Catalog_Model_Design::APPLY_FOR_PRODUCT);
            $this->_initProductLayout($product);
            $this->_initLayoutMessages('catalog/session');
            $this->_initLayoutMessages('tag/session');
            $this->_initLayoutMessages('checkout/session');
            $this->renderLayout();
        } else {
            if (isset($_GET['store'])  && !$this->getResponse()->isRedirect()) {
                $this->_redirect('');
            } elseif (!$this->getResponse()->isRedirect()) {
                $this->_forward('noRoute');
            }
        }
    }
    public function addToCartPopupAction()
    {
    	$_product_Id = $this->getRequest()->getParam('productId','');
    	$_child_product_id = $this->getRequest()->getParam('childProductId','');
    	$_child_product_Type = $this->getRequest()->getParam('childProductType','');
    	$_cartUrl = Mage::helper('zcatalog')->getGroupedProductCartUrl($_product_Id,$_child_product_id,$_child_product_Type);
    	//$this->_redirect($_cartUrl);
    	$data['url']= $_cartUrl;
    	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($data));
    }
}
