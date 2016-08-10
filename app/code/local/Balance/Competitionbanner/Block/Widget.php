<?php

class Balance_Competitionbanner_Block_Widget
    extends Mage_Core_Block_Template
    implements Mage_Widget_Block_Interface
{

    /**
     * Produces links list html
     *
     * @return string
     */
   /* protected function _toHtml()
    {
        return $html = '';

        $categoryId = $this->getData('category');
        if (empty($categoryId)) {
            $this->assign('list', false);
        }

        $pdflist = Mage::getModel("balance_pdfwidget/pdf")->getCollection()->addFieldToFilter("category", $categoryId)->addFieldToFilter("link", array("nin" => (array(NULL, ""))));

        $this->assign('list', $pdflist);
        return parent::_toHtml();
    }*/

    protected function _toHtml()
    {
        $html = '';

        if ($this->getData('imagepath') == '') {
            return $html;
        }
        /*if ( $_COOKIE["banneroff"] ||  !$this->getData('visibility')) {
            return $html;
        }*/
        if (Mage::app()->getCookie()->get("banneroffp") || $_COOKIE["banneroff"] || !$this->getData('visibility')) {
            return $html;
        }
        $this->imageAttributes = $this->getData('imagepath');
        return parent::_toHtml();
    }

}
