<?php

class Balance_Varnish_Model_Control_Cms_Page
    extends Balance_Varnish_Model_Control_Abstract
{
    const XML_PATH_WEB_DEFAULT_CMS_HOME_PAGE = 'web/default/cms_home_page';

    protected $_helperName = 'varnish/control_cms_page';

    /**
     * Purge Cms Page
     *
     * @param Mage_Cms_Model_Page $page
     *
     * @return Balance_Varnish_Model_Control_Cms_Page
     */
    public function purge(Mage_Cms_Model_Page $page)
    {
        if ($this->_canPurge()) {

            $storeIds = Mage::getResourceModel('varnish/cms_page_store_collection')
                ->addPageFilter($page->getId())
                ->getAllIds();

            if (count($storeIds) && current($storeIds) == 0) {
                $storeIds = Mage::getResourceModel('core/store_collection')
                    ->setWithoutDefaultFilter()
                    ->getAllIds();
            }

            foreach ($storeIds as $storeId) {
                $url = Mage::app()->getStore($storeId)
                    ->getUrl(null, array('_direct' => $page->getIdentifier()));
                extract(parse_url($url));
                $path = rtrim($path, '/');
                $this->_getCacheControl()->clean($host, '^' . $path . '/{0,1}$');

                // Purge if current page is a home page
                $homePageIdentifier
                    = Mage::getStoreConfig(self::XML_PATH_WEB_DEFAULT_CMS_HOME_PAGE, $storeId);
                if ($page->getIdentifier() == $homePageIdentifier) {
                    $url = Mage::app()->getStore($storeId)
                        ->getUrl();
                    extract(parse_url($url));
                    $path = rtrim($path, '/');
                    $this->_getCacheControl()->clean($host, '^' . $path . '/{0,1}$');
                    $this->_getCacheControl()->clean($host, '^/{0,1}$');
                }
            }

            $this->_getSession()->addSuccess(
                Mage::helper('varnish')->__('Varnish cache for "%s" has been purged.', $page->getTitle())
            );

        }

        return $this;
    }

}
