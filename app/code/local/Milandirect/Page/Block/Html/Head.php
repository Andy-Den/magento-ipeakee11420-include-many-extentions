<?php

/**
 * Html page block
 *
 * @category  Milandirect
 * @package   Milandirect_OneStepCheckout
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2015 Balance
 */
class Milandirect_Page_Block_Html_Head extends MageWorx_SeoSuite_Block_Page_Html_Head
{

    /**
     * Merge static and skin files of the same format into 1 set of HEAD directives or even into 1 directive
     *
     * Will attempt to merge into 1 directive, if merging callback is provided. In this case it will generate
     * filenames, rather than render urls.
     * The merger callback is responsible for checking whether files exist, merging them and giving result URL
     *
     * @param string $format - HTML element format for sprintf('<element src="%s"%s />', $src, $params)
     * @param array $staticItems - array of relative names of static items to be grabbed from js/ folder
     * @param array $skinItems - array of relative names of skin items to be found in skins according to design config
     * @param callback $mergeCallback
     * @return string
     */
    protected function &_prepareStaticAndSkinElements($format, array $staticItems, array $skinItems, $mergeCallback = null)
    {
        $designPackage = Mage::getDesign();
        $baseJsUrl = Mage::getBaseUrl('js');
        $items = array();
        if ($mergeCallback && !is_callable($mergeCallback)) {
            $mergeCallback = null;
        }
        // get static files from the js folder, no need in lookups
        foreach ($staticItems as $params => $rows) {
            foreach ($rows as $name) {
                $items[$params][] = $mergeCallback ? Mage::getBaseDir() . DS . 'js' . DS . $name : $baseJsUrl . $name;
            }
        }

        // lookup each file basing on current theme configuration
        $newParam = '';
        foreach ($skinItems as $params => $rows) {
            foreach ($rows as $name) {
                if ($newParam == '' && $name != 'css/site2.css') {
                    $items[$params][] = $mergeCallback ? $designPackage->getFilename($name, array('_type' => 'skin'))
                        : $designPackage->getSkinUrl($name, array());
                }
                elseif ($name == 'css/site3.css' || $name == 'css/magestore/sociallogin.css' || $name == 'css/magestore/mobilesociallogin.css' ) {
                    $items["media='all'"][] = $mergeCallback ? $designPackage->getFilename($name, array('_type' => 'skin'))
                        : $designPackage->getSkinUrl($name, array());
                }
                else {
                    $newParam = 'media="all" ';
                    $items[$newParam][] = $mergeCallback ? $designPackage->getFilename($name, array('_type' => 'skin'))
                        : $designPackage->getSkinUrl($name, array());
                }
            }
        }

        $html = '';
        foreach ($items as $params => $rows) {
            // attempt to merge
            $mergedUrl = false;
            if ($mergeCallback) {
                $mergedUrl = call_user_func($mergeCallback, $rows);
            }
            // render elements
            $params = trim($params);
            $params = $params ? ' ' . $params : '';
            if ($mergedUrl) {
                $html .= sprintf($format, $mergedUrl, $params);
            } else {
                foreach ($rows as $src) {
                    $html .= sprintf($format, $src, $params);
                }
            }
        }
        return $html;
    }

    /**
     * Get robots
     * @return string
     */
    public function getRobots()
    {
        if ($category = Mage::registry('current_category')) {
            $category = Mage::getModel('catalog/category')->load($category->getId());
            if ($category) {
                $robotCategoryValue = $category->getData('meta_robots');
                $this->_data['robots'] = $this->getRobotText($robotCategoryValue);
                return $this->_data['robots'];
            } else {
                return parent::getRobots();
            }

        } elseif ($_product = Mage::registry('current_product')) {
            $product = Mage::getModel('catalog/product')->load($_product->getId());
            if ($product) {
                $categoryIds = $product->getCategoryIds();
                if (is_array($categoryIds) and count($categoryIds) > 1) {
                    $category = Mage::getModel('catalog/category')->load($categoryIds[0]);
                    $robotCategoryValue = $category->getData('meta_robots');
                    $this->_data['robots'] = $this->getRobotText($robotCategoryValue);
                    return $this->_data['robots'];
                } else {
                    return parent::getRobots();
                }
            } else {
                return parent::getRobots();
            }
        } else {
            return parent::getRobots();
        }
    }

    /**
     * Get Robot text from robot Id
     * @param int $robotId robot Id
     * @return mixed|string
     */
    public function getRobotText($robotId)
    {
        switch ($robotId) {
            case 1:
                $robotName = 'INDEX, FOLLOW';
                break;
            case 2:
                $robotName = 'NOINDEX, FOLLOW';
                break;
            case 3:
                $robotName = 'INDEX, NOFOLLOW';
                break;
            case 4:
                $robotName = 'NOINDEX, NOFOLLOW';
                break;
            default:
                $robotName = Mage::getStoreConfig('design/head/default_robots');
                break;
        }

        return $robotName;
    }

    /**
     * Set link rel meta
     * @return bool
     */
    public function setLinkRel()
    {
        if (!Mage::helper('seosuite')->isLinkRelEnabled()) return false;

        $actionName = $this->getAction()->getFullActionName();
        if ($actionName=='catalog_category_view' || $actionName=='tag_product_list' || $actionName=='catalogsearch_result_index' || $actionName=='review_product_list' || $actionName == 'vendors_index_index') {
            if ($actionName=='catalog_category_view' || $actionName=='catalogsearch_result_index') {
                // Category Page + Layer + Search
                switch ($actionName) {
                    case 'catalog_category_view':
                        $collection = $this->getLayout()->getBlock('product_list');
                        break;
                    case 'catalogsearch_result_index':
                        $collection = $this->getLayout()->getBlock('search_result_list');
                        break;
                }
                if (!is_object($collection)) {
                    $collection = $this->getLayout()->createBlock('catalog/product_list')->getLoadedProductCollection();
                }
                $toolbar = $this->getLayout()->createBlock('page/html_pager')->setLimit($this->getLayout()->createBlock('catalog/product_list_toolbar')->getLimit())->setCollection($collection);
            } else if ($actionName=='review_product_list') {
                // Reviews
                $collection = $this->getLayout()->createBlock('review/product_view_list')->getReviewsCollection();
                $toolbar = $this->getLayout()->createBlock('page/html_pager')->setLimit($this->getLayout()->createBlock('catalog/product_list_toolbar')->getLimit())->setCollection($collection);
            } else if ($actionName=='tag_product_list') {
                // Tags
                $tag = Mage::registry('current_tag');
                if (!$tag) return false;
                $collection = $tag->getEntityCollection()
                    ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                    ->addTagFilter($tag->getId())
                    ->addStoreFilter(Mage::app()->getStore()->getId())
                    ->addMinimalPrice()
                    ->addUrlRewrite()
                    ->setActiveFilter();
                Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($collection);
                Mage::getSingleton('catalog/product_visibility')->addVisibleInSiteFilterToCollection($collection);

                // tags
                $toolbar = $this->getLayout()
                    ->createBlock('page/html_pager')
                    ->setLimit($this->getLayout()->createBlock('catalog/product_list_toolbar')->getLimit())
                    ->setCollection($collection);
            } else if ($actionName == 'vendors_index_index') {
                $collection = Mage::getModel('udropship/vendor')->getCollection();
                $collection->getSelect()->joinLeft(array('vs' => Mage::getSingleton('core/resource')->getTableName('vendors_shop')), 'main_table.vendor_id = vs.vendor_id  ', array('vs.*'));
                $collection->addFieldToFilter('main_table.status', array("eq" => "A"));
                $collection->addFieldToFilter('vs.shop_status ', array("eq" => "1"));
                $toolbar = $this->getLayout()->createBlock('page/html_pager')->setLimit(Mage::getStoreConfig('udropship/vendor/vendor_page_size'))->setCollection($collection);
            }

            $linkPrev = false;
            $linkNext = false;
            if ($toolbar->getCollection()->getSelectCountSql()) {
                if ($toolbar->getLastPageNum() > 1) {
                    if (!$toolbar->isFirstPage()) {
                        $linkPrev = true;
                        if ($toolbar->getCurrentPage() == 2) {
                            // remove p=1
                            $prevUrl = str_replace(array('?p=1&amp;', '?p=1&', '&amp;p=1&amp;', '&p=1&'), array('?', '?', '&amp;', '&'), $toolbar->getPreviousPageUrl());
                            if (substr($prevUrl, -4)=='?p=1') {
                                $prevUrl = substr($prevUrl, 0, -4);
                                $prevUrl = Mage::helper('seosuite')->_trailingSlash($prevUrl);
                            } elseif (substr($prevUrl, -8)=='&amp;p=1') {
                                $prevUrl = substr($prevUrl, 0, -8);
                            } elseif (substr($prevUrl, -4)=='&p=1') {
                                $prevUrl = substr($prevUrl, 0, -4);
                            }
                        }
                        else {
                            $prevUrl = $toolbar->getPreviousPageUrl();
                        }
                    }
                    if (!$toolbar->isLastPage()) {
                        $linkNext = true;
                        $nextUrl = $toolbar->getNextPageUrl();
                    }
                }
            }
//            if ($linkPrev) echo '<link rel="prev" href="' . $prevUrl . '" />';
//            if ($linkNext) echo '<link rel="next" href="' . $nextUrl . '" />';
            if ($linkPrev) $this->addLinkRel('prev', $prevUrl);
            if ($linkNext) $this->addLinkRel('next', $nextUrl);

        }

    }

    /**
     * Get meta title
     * @return string
     */
    public function getTitle()
    {
        $origTitle = (isset($this->_data['title'])?$this->_data['title']:'');
        if ($this->getAction()->getFullActionName() == 'xsitemap_index_index') {
            $this->_data['title'] = Mage::getStoreConfig('mageworx_seo/xsitemap/sitemap_meta_title');
        } else if (Mage::registry('current_product')) {
            $this->_product = Mage::registry('current_product');
            $title = '';
            if (!$this->_product->getMetaTitle()) {
                $titleTemplate = Mage::getStoreConfig('mageworx_seo/seosuite/product_meta_title');
                $template = Mage::getModel('seosuite/catalog_product_template_title');
                $template->setTemplate($titleTemplate)
                    ->setProduct($this->_product);
                $title = $template->process();
            }
            if ($title) $this->_data['title'] = $title;
        } elseif (Mage::app()->getRequest()->getModuleName()=='cms') {
            $title = Mage::getSingleton('cms/page')->getMetaTitle();
            if ($title) $this->_data['title'] = $title;
        }

        $this->_convertLayerMeta();

        if (!isset($this->_data['title']) || empty($this->_data['title'])) {
            $this->_data['title'] = $this->getDefaultTitle();
        } else if ($origTitle!=$this->_data['title']) {
            // add prefix and suffix
            $this->setTitle($this->_data['title']);
        }

        if($this->getAction()->getFullActionName() == 'catalog_product_view'){
            $product = Mage::registry('current_product');
            if($product){
                $special_price = $product->getSpecialPrice();
                $regular_price = $product->getPrice();
                $special_from = $product->getSpecialFromDate();
                $special_to = $product->getSpecialToDate();
                $date = Mage::getModel('core/date')->date('Y-m-d H:i:s');
                if($special_price > 0 && (($special_from <= $date && $date <= $special_to) || ($special_from <= $date && is_null($special_to)))){
                    $discount = (($regular_price - $special_price) * 100) / $regular_price;
                    $this->_data['title'] = $product->getName() . " " . round($discount) . "% OFF | " . Mage::helper('core')->currency($special_price, true, false) . " - Milan Direct";
                } else {
                    $this->_data['title'] = $product->getName() . " | " . Mage::helper('core')->currency($regular_price, true, false) . " - Milan Direct";;
                }
            }
        }

        return trim(htmlspecialchars(html_entity_decode($this->_data['title'], ENT_QUOTES, 'UTF-8')));
    }

    /**
     * Convert layer meta
     * @return bool
     */
    private function _convertLayerMeta()
    {
        // if not product page
        if (Mage::registry('current_category')==null || Mage::registry('current_product')!=null) return false;

        $helper = Mage::helper('seosuite');
        $request = Mage::app()->getRequest();

        $hideAttributes = Mage::getStoreConfigFlag('mageworx_seo/seosuite/layered_hide_attributes');
        $layeredFriendlyUrls = Mage::getStoreConfigFlag('mageworx_seo/seosuite/layered_friendly_urls');


        $params = Mage::app()->getRequest()->getParams();

        if (Mage::registry('current_category') != null) {

            // get meta title
            $metaTitle = Mage::registry('current_category')->getMetaTitle();
            if (!$metaTitle) $metaTitle = Mage::registry('current_category')->getName();
            if (!Mage::getStoreConfigFlag('mageworx_seo/seosuite/enable_dynamic_meta_title')) {
                $metaTitle = $this->__compile($metaTitle);
            }
            $this->_data['title'] = $metaTitle;

            // get meta description
            $metaDescription = Mage::registry('current_category')->getMetaDescription();
            if (!$metaDescription) $metaDescription = Mage::registry('current_category')->getDescription();
            if (!Mage::getStoreConfigFlag('mageworx_seo/seosuite/enable_dynamic_meta_desc')) {
                $metaDescription = $this->__compile($metaDescription);
            }
            $this->_data['description'] = $metaDescription;
        }

        if ($layeredFriendlyUrls) {
            $suffix = Mage::getStoreConfig('catalog/seo/category_url_suffix');
            $identifier = trim(str_replace($suffix, '', $request->getOriginalPathInfo()), '/');
            $urlSplit = explode('/l/', $identifier, 2);
            if (!isset($urlSplit[1])) return false;
            Varien_Autoload::registerScope('catalog');
            $productUrl = Mage::getModel('catalog/product_url');
            list($cat, $params) = $urlSplit;
            $layerParams = explode('/', $params);
            $_params = array();

            $descParts = array();

            // from registry
            $attr = $helper->_getFilterableAttributes();

            $titleParts = array(trim($this->_data['title']));
            if (isset($this->_data['description']) && trim($this->_data['description'])) {
                $descParts = array(trim($this->_data['description']));
            }
            if (count($layerParams)) {
                foreach ($layerParams as $params) {
                    $param = explode($helper->getAttributeParamDelimiter(), $params, 2);
                    if (count($param) == 1) {
                        $cat = Mage::getModel('seosuite/catalog_category')
                            ->setStoreId(Mage::app()->getStore()->getId())
                            ->loadByAttribute('url_key', $productUrl->formatUrlKey($param[0]));
                        if ($cat && $cat->getId()) {
                            $titleParts[0] .= ' - ' . $cat->getName();
                            continue;
                        }
                        foreach ($attr as $attribute) {
                            if (isset($attribute['options'][current($param)])) {
                                $titleParts[] = $descParts[] = $attribute['options'][current($param)];
                                break;
                            }
                        }
                    } else {
                        $code = str_replace('-', '_', $param[0]); // attrCode is only = [a-z0-9_]
                        if (isset($attr[$code])) {
                            if ($code == 'price') {
                                $multipliers = explode(',', $param[1]);
                                $frontendLabel = $hideAttributes ? '' : (isset($attr[$code]['frontend_label'])?$attr[$code]['frontend_label']:'');
                                if (isset($multipliers[1])) {
                                    $titleParts[] = $descParts[] = $frontendLabel . ' ' . Mage::app()->getStore()->formatPrice($multipliers[0] * $multipliers[1] - $multipliers[1], false) . ' - ' . Mage::app()->getStore()->formatPrice($multipliers[0] * $multipliers[1], false);
                                }
                                continue;
                            }
                            if (isset($attr[$code]['frontend_label']) && isset($attr[$code]['options'][$param[1]])) $titleParts[] = $descParts[] = $attr[$code]['frontend_label'] . ' - ' . $attr[$code]['options'][$param[1]];
                        }
                    }
                }
            }
            if (Mage::getStoreConfigFlag('mageworx_seo/seosuite/enable_dynamic_meta_title')) {
                $this->_data['title'] = implode(', ', $titleParts);
            }

            if (Mage::getStoreConfigFlag('mageworx_seo/seosuite/enable_dynamic_meta_desc')) {
                $this->_data['description'] = implode(', ', $descParts);
            }
        }
    }
}
