<?php
require_once(Mage::getModuleDir('controllers','Innobyte_ProductQuestions').DS.'IndexController.php');
class Milandirect_ProductQuestions_IndexController extends Innobyte_ProductQuestions_IndexController
{

    public function reloadAction()
    {
        // check if is ajax request
        if (!$this->getRequest()->isXmlHttpRequest()) {
            die('Not allowed');
        }

        $request = $this->getRequest();

        // Get current store id 
        $storeId = Mage::app()->getStore()->getId();
        $productId = $request->getParam('product');

        $productObj = Mage::getModel('catalog/product')->load($productId);
        Mage::register('product', $productObj);

        $sortBy = $request->getParam('sortby');
        $sortDir = $request->getParam('sort');
        $limitMultiplier = $request->getParam('limit');

        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->load('catalog_product_view');
        $layout->generateXml()->generateBlocks();

        $block = $layout->getBlock('inno.product_questions_list');
        $block->setFilters($sortBy, $sortDir, $limitMultiplier);
        $html = $block->setTemplate('innobyte/product_questions/list.phtml')->toHtml();

        echo $html;
        exit;
    }

}
