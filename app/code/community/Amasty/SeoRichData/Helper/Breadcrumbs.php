<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoRichData
 */

class Amasty_SeoRichData_Helper_Breadcrumbs extends Mage_Core_Helper_Abstract
{
    const ITEM_TYPE_BREADCRUMB_LIST  = 'https://schema.org/BreadcrumbList';
    const ITEM_TYPE_LIST_ITEM        = 'https://schema.org/ListItem';

    public function apply($html)
    {
        $containerSelector = Mage::getStoreConfig('amseorichdata/breadcrumbs/container_selector');
        $itemSelector = Mage::getStoreConfig('amseorichdata/breadcrumbs/item_selector');
        $urlSelector = Mage::getStoreConfig('amseorichdata/breadcrumbs/url_selector');
        $titleSelector = Mage::getStoreConfig('amseorichdata/breadcrumbs/title_selector');

        $dom = new Amasty_SeoRichData_Model_Dom($html);


        $containerNodes = $dom->queryAll($containerSelector);

        if ($containerNodes->length != 1)
            return $html;

        $containerNode = $containerNodes->item(0);
        $containerNode->setAttribute('itemscope', '');
        $containerNode->setAttribute('itemtype',  self::ITEM_TYPE_BREADCRUMB_LIST);

        $itemNodes = $dom->queryAll($itemSelector);
        $urlNodes = $dom->queryAll($urlSelector);
        $titleNodes = $dom->queryAll($titleSelector);
        $count = $urlNodes->length;

        $itemCounter = $urlCounter = $titleCounter = 0;

        foreach ($itemNodes as $item)
        {
            $itemCounter++;
            if ($itemCounter <= $count) {
                $item->setAttribute('itemscope', '');
                $item->setAttribute('itemprop', 'itemListElement');
                $item->setAttribute('itemtype', self::ITEM_TYPE_LIST_ITEM);
            }
        }


        foreach ($urlNodes as $url) {
            $urlCounter++;
            if ($urlCounter <= $count) {
                $url->setAttribute('itemprop', 'item');
            }
        }

        $customTag = Mage::helper('amseorichdata/htmlManager')->getCustomTag();
        foreach ($titleNodes as $title)
        {
            $titleCounter++;
            if ($titleCounter <= $count) {
                $wrap = $dom->getDocument()->createElement($customTag);
                $wrap->setAttribute('itemprop', 'name');

                while ($title->childNodes->length > 0)
                {
                    $child = $title->childNodes->item(0);

                    $wrap->appendChild($child);
                }
                $title->appendChild($wrap);
            }

        }

        return $dom->save();
    }
}
