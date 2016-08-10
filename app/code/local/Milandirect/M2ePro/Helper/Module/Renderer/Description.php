<?php

/**
 * Rewrite M2ePro helper
 *
 * @category  Milandirect
 * @package   Milandirect_M2ePro
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2016 Balance
 */
class Milandirect_M2ePro_Helper_Module_Renderer_Description extends Ess_M2ePro_Helper_Module_Renderer_Description
{
    const IMAGES_MODE_DEFAULT    = 0;
    const IMAGES_MODE_NEW_WINDOW = 1;
    const IMAGES_MODE_GALLERY    = 2;

    const IMAGES_QTY_ALL = 0;

    const LAYOUT_MODE_ROW    = 'row';
    const LAYOUT_MODE_COLUMN = 'column';

    /**
     * Parse template description
     * @param string                           $text           html description config
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct m2epro product
     * @return mixed|string
     * @throws Ess_M2ePro_Model_Exception
     */
    public function parseTemplate($text, Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $design = Mage::getDesign();

        $oldArea = $design->getArea();
        $oldStore = Mage::app()->getStore();
        $oldPackageName = $design->getPackageName();

        $design->setArea('adminhtml');
        Mage::app()->setCurrentStore(Mage::app()->getStore($magentoProduct->getStoreId()));
        $design->setPackageName(Mage::getStoreConfig('design/package/name', Mage::app()->getStore()->getId()));

        $text = $this->insertAttributes($text, $magentoProduct);
        $text = $this->insertImages($text, $magentoProduct);
        $text = $this->insertMediaGalleries($text, $magentoProduct);

        //  the CMS static block replacement i.e. {{media url=’image.jpg’}}
        $filter = new Mage_Core_Model_Email_Template_Filter();
        $filter->setVariables(array('product'=>$magentoProduct->getProduct()));

        $text = $filter->filter($text);

        $design->setArea($oldArea);
        Mage::app()->setCurrentStore($oldStore);
        $design->setPackageName($oldPackageName);

        return $text;
    }

    /**
     * Insert attribute to description on M2e
     *
     * @param string                           $text           description data from M2e config
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct m2e product
     * @return mixed
     * @throws Ess_M2ePro_Model_Exception
     */
    private function insertAttributes($text, Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        preg_match_all('/#([a-zA-Z_0-9]+?)#/', $text, $matches);

        if (!count($matches[0])) {
            return $text;
        }

        $search = array();
        $replace = array();
        $valueOverviewSpecifications = $this->parseHtmlTags(
            $magentoProduct->getAttributeValue('overview_specifications'),
            'div'
        );
        foreach ($matches[1] as $attributeCode) {

            $value = $magentoProduct->getAttributeValue($attributeCode);

            if (!is_array($value) && $value != '') {
                if ($attributeCode == 'description') {
                    $value = $this->normalizeDescription($value);
                } elseif ($attributeCode == 'weight') {
                    $value = (float)$value;
                } elseif (in_array($attributeCode, array('price', 'special_price'))) {
                    $value = round($value, 2);
                    $storeId = $magentoProduct->getProduct()->getStoreId();
                    $store = Mage::app()->getStore($storeId);
                    $value = $store->formatPrice($value, false);
                }
                $search[] = '#' . $attributeCode . '#';
                if ($attributeCode == 'overview_specifications') {
                    $value = '';
                }
                $replace[] = $value;
            } else {
                $search[] = '#' . $attributeCode . '#';
                $value = '';
                $_product = $magentoProduct->getProduct();
                if ($attributeCode == 'average_product_rating') {
                    $value = $this->getAverageProductRating($_product);
                }

                if ($attributeCode == 'product_url') {
                    $value = $_product->getProductUrl();
                }

                if ($attributeCode == 'latest_product_review') {
                    $value = $this->getLatestProductReview($_product);
                }

                if ($attributeCode == 'latest_news') {
                    $storeIds = $magentoProduct->getProduct()->getStoreIds();
                    if (count($storeIds) > 1) {
                        $storeIds = $storeIds[0];
                    }
                    $value = $this->getLatestNews($storeIds);
                }

                if (is_array($valueOverviewSpecifications)) {
                    if ($attributeCode == 'overview') {
                        $value = $valueOverviewSpecifications[0];
                    }

                    if ($attributeCode == 'specifications') {
                        $value = $valueOverviewSpecifications[count($valueOverviewSpecifications)-1];
                    }
                }

                if ($attributeCode == 'media') {
                    $value = $this->getMediaGallery($text, $magentoProduct);
                }
                $replace[] = $value;
            }
        }

        $text = str_replace($search, $replace, $text);

        return $text;
    }

    /**
     * @param string                           $text           gallery config
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct m2epro product
     * @return mixed
     */
    private function insertImages($text, Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        preg_match_all("/#image\[(.*?)\]#/", $text, $matches);

        if (!count($matches[0])) {
            return $text;
        }

        $imageLink = $magentoProduct->getImageLink('image');

        $blockObj = Mage::getSingleton('core/layout')->createBlock(
            'M2ePro/adminhtml_renderer_description_image'
        );

        $search = array();
        $replace = array();
        foreach ($matches[0] as $key => $match) {

            $tempImageAttributes = explode(',', $matches[1][$key]);
            $realImageAttributes = array();
            for ($i=0; $i<6; $i++) {
                if (!isset($tempImageAttributes[$i])) {
                    $realImageAttributes[$i] = 0;
                } else {
                    $realImageAttributes[$i] = (int)$tempImageAttributes[$i];
                }
            }

            $tempImageLink = $realImageAttributes[5] == 0
                ? $imageLink
                : $magentoProduct->getGalleryImageLink($realImageAttributes[5]);

            $data = array(
                'width'       => $realImageAttributes[0],
                'height'      => $realImageAttributes[1],
                'margin'      => $realImageAttributes[2],
                'linked_mode' => $realImageAttributes[3],
                'watermark'   => $realImageAttributes[4],
                'src'         => $tempImageLink
            );
            $search[] = $match;
            $replace[] = ($tempImageLink == '')
                ? '' :
                preg_replace('/\s{2,}/', '', $blockObj->addData($data)->toHtml());
        }

        $text = str_replace($search, $replace, $text);

        return $text;
    }

    /**
     * Get media gallery from config
     *
     * @param string                           $text           media gallery config
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct m2epro product
     * @return mixed
     */
    private function insertMediaGalleries($text, Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        preg_match_all("/#media_gallery\[(.*?)\]#/", $text, $matches);

        if (!count($matches[0])) {
            return $text;
        }

        $blockObj = Mage::getSingleton('core/layout')->createBlock(
            'M2ePro/adminhtml_renderer_description_gallery'
        );

        $search = array();
        $replace = array();
        $attributeCounter = 0;
        foreach ($matches[0] as $key => $match) {
            $tempMediaGalleryAttributes = explode(',', $matches[1][$key]);
            $realMediaGalleryAttributes = array();
            for ($i=0; $i<8; $i++) {
                if (!isset($tempMediaGalleryAttributes[$i])) {
                    $realMediaGalleryAttributes[$i] = '';
                } else {
                    $realMediaGalleryAttributes[$i] = $tempMediaGalleryAttributes[$i];
                }
            }

            $imagesQty = (int)$realMediaGalleryAttributes[5];
            if ($imagesQty == self::IMAGES_QTY_ALL) {
                $imagesQty = $realMediaGalleryAttributes[3] == self::IMAGES_MODE_GALLERY ? 100 : 25;
            }

            $galleryImagesLinks = $magentoProduct->getGalleryImagesLinks($imagesQty);
            if (!count($galleryImagesLinks)) {
                $search = $matches[0];
                $replace = '';
                break;
            }

            if (!in_array($realMediaGalleryAttributes[4], array(self::LAYOUT_MODE_ROW, self::LAYOUT_MODE_COLUMN))) {
                $realMediaGalleryAttributes[4] = self::LAYOUT_MODE_ROW;
            }

            $data = array(
                'width'        => (int)$realMediaGalleryAttributes[0],
                'height'       => (int)$realMediaGalleryAttributes[1],
                'margin'       => (int)$realMediaGalleryAttributes[2],
                'linked_mode'  => (int)$realMediaGalleryAttributes[3],
                'layout'       => $realMediaGalleryAttributes[4],
                'gallery_hint' => trim($realMediaGalleryAttributes[6], '"'),
                'watermark' => (int)$realMediaGalleryAttributes[7],
                'images_count' => count($galleryImagesLinks),
                'image_counter' => 0
            );

            $tempHtml = '';
            $attributeCounter++;

            foreach ($galleryImagesLinks as $imageLink) {
                $data['image_counter']++;
                $data['attribute_counter'] = $attributeCounter;
                $data['src'] = $imageLink;
                $tempHtml .= $blockObj->addData($data)->toHtml();
            }
            $search[] = $match;
            $replace[] = preg_replace('/\s{2,}/', '', $tempHtml);
        }

        $text = str_replace($search, $replace, $text);

        return $text;
    }

    /**
     * Used to get media gallery
     *
     * @param string                          $text           gallery config
     * @param Ess_M2ePro_Model_MagentoProduct $magentoProduct m2epro product
     * @return string
     */
    private function getMediaGallery($text, Ess_M2ePro_Model_MagentoProduct $magentoProduct)
    {
        preg_match_all("/#media#/", $text, $matches);
        if (!count($matches[0])) {
            return $text;
        }

        $blockObj = Mage::getSingleton('core/layout')->createBlock('M2ePro/adminhtml_renderer_description_gallery');

        $search = array();
        $replace = array();
        $attributeCounter = 0;
        foreach ($matches[0] as $key => $match) {
            $tempMediaGalleryAttributes = explode(',', $matches[1][$key]);
            $realMediaGalleryAttributes = array();
            for ($i=0; $i<7; $i++) {
                if (!isset($tempMediaGalleryAttributes[$i])) {
                    $realMediaGalleryAttributes[$i] = '';
                } else {
                    $realMediaGalleryAttributes[$i] = $tempMediaGalleryAttributes[$i];
                }
            }

            $imagesQty = (int)$realMediaGalleryAttributes[5];
            if ($imagesQty == self::IMAGES_QTY_ALL) {
                $imagesQty = $realMediaGalleryAttributes[3] == self::IMAGES_MODE_GALLERY ? 100 : 25;
            }

            $galleryImagesLinks = $magentoProduct->getGalleryImagesLinks($imagesQty);
            if (!count($galleryImagesLinks)) {
                $search = $matches[0];
                $replace = '';
                break;
            }

            if (!in_array($realMediaGalleryAttributes[4], array(self::LAYOUT_MODE_ROW, self::LAYOUT_MODE_COLUMN))) {
                $realMediaGalleryAttributes[4] = self::LAYOUT_MODE_ROW;
            }

            $data = array(
                'width'        => 144,
                'height'       => 108,
                'margin'       => (int)$realMediaGalleryAttributes[2],
                'linked_mode'  => (int)$realMediaGalleryAttributes[3],
                'layout'       => $realMediaGalleryAttributes[4],
                'gallery_hint' => trim($realMediaGalleryAttributes[6], '"'),
                'watermark' => (int)$realMediaGalleryAttributes[7],
                'images_count' => count($galleryImagesLinks),
                'image_counter' => 0
            );

            $tempHtml = '';
            $attributeCounter++;

            foreach ($galleryImagesLinks as $imageLink) {
                $data['image_counter']++;
                $data['attribute_counter'] = $attributeCounter;
                $data['src'] = $imageLink;
                $tempHtml .= $blockObj->addData($data)->toHtml();
            }
            $search[] = $match;
            $replace[] = preg_replace('/\s{2,}/', '', $tempHtml);
        }
        return $tempHtml;
    }

    /**
     * Prepare description
     *
     * @param string $str description
     * @return mixed|string
     */
    private function normalizeDescription($str)
    {
        // Trim whitespace
        if (($str = trim($str)) === '') {
            return '';
        }

        // Standardize newlines
        $str = str_replace(array("\r\n", "\r"), "\n", $str);

        // Trim whitespace on each line
        $str = preg_replace('~^[ \t]+~m', '', $str);
        $str = preg_replace('~[ \t]+$~m', '', $str);

        // The following regexes only need to be executed if the string contains html
        if ($html_found = (strpos($str, '<') !== false)) {
            // Elements that should not be surrounded by p tags
            $no_p  = '(?:p|div|h[1-6r]|ul|ol|li|blockquote|d[dlt]|pre|t[dhr]|t(?:able|body|foot|head)|';
            $no_p .= 'c(?:aption|olgroup)|form|s(?:elect|tyle)|a(?:ddress|rea)|ma(?:p|th))';

            // Put at least two linebreaks before and after $no_p elements
            $str = preg_replace('~^<' . $no_p . '[^>]*+>~im', "\n$0", $str);
            $str = preg_replace('~</' . $no_p . '\s*+>$~im', "$0\n", $str);
        }

        // Do the p element magic!
        $str = '<p>' . trim($str) . '</p>';
        $str = preg_replace('~\n{2,}~', "</p>\n\n<p>", $str);

        // The following regexes only need to be executed if the string contains html
        if ($html_found !== false) {
            // Remove p tags around $no_p elements
            $str = preg_replace('~<p>(?=</?' . $no_p . '[^>]*+>)~i', '', $str);
            $str = preg_replace('~(</?' . $no_p . '[^>]*+>)</p>~i', '$1', $str);
        }

        // Convert single linebreaks to <br/>
        $br = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/renderer/description/','convert_linebreaks');
        if (is_null($br) || (bool)(int)$br === true) {
            $str = preg_replace('~(?<!\n)\n(?!\n)~', "<br/>\n", $str);
        }

        return $str;
    }

    /**
     * Parse html with tag
     *
     * @param string $html html data
     * @param string $tag  html tab
     * @return array
     */
    private function parseHtmlTags($html, $tag)
    {
        $nodeValues = array();
        $doc = new DOMDocument();
        @$doc->loadHTML($html);
        $nodes = $doc->getElementsByTagName($tag);
        foreach ($nodes as $node) {
            if (!empty($node->nodeValue)) {
                $nodeValues[] = $this->getInnerHTML($node);
            }
        }
        return $nodeValues;
    }

    /**
     * Get html from element
     *
     * @param string $element html element
     * @return string
     */
    private function getInnerHTML($element)
    {
        $innerHTML = "";
        $children = $element->childNodes;
        foreach ($children as $child) {
            $tmp_dom = new DOMDocument();
            $tmp_dom->appendChild($tmp_dom->importNode($child, true));
            $innerHTML.=trim($tmp_dom->saveHTML());
        }
        return $innerHTML;
    }
}
