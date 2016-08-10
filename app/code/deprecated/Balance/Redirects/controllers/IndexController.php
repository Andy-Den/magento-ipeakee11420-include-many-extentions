<?php

require_once "Mage/Cms/controllers/IndexController.php";

class Balance_Redirects_IndexController extends Mage_Cms_IndexController
{

    /**
     * Render CMS 404 Not found page
     *
     * @param string $coreRoute
     */
    public function noRouteAction($coreRoute = null)
    {

        $identifier = trim($this->getRequest()->getRequestString()); //  ->getPathInfo());
        $identifier = ltrim($identifier, '/');
        $identifier = rtrim($identifier, '/');
        $urlSplits = explode('/', $identifier);

        // Not found redirect to front page.
        if (count($urlSplits) == 1) {
            $this->getResponse()->setHeader('HTTP/1.1', '301 Moved Permanently');
            $this->getResponse()->setHeader('Location', Mage::getBaseUrl());
        } else {
            // Recursively redirect to category URL if it exists.
            array_pop($urlSplits);
            $noRounter  =   true;
            do {
                //
                $path = null;
                foreach ($urlSplits as $urlSplit) {
                    $path .= $urlSplit . '/';
                }
                $rewrite = Mage::getModel('core/url_rewrite')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->loadByRequestPath($path);
                if ($rewrite->getCategoryId()) {
                    $noRounter  =   false;
                    $redirectUrl = $this->constructUrl($urlSplits);
                    $this->getResponse()->setHeader('HTTP/1.1', '301 Moved Permanently');
                    $this->getResponse()->setHeader('Location', $redirectUrl);
                    break;
                }
                array_pop($urlSplits);
            } while (count($urlSplits) > 0);
            if($noRounter==true){
                $this->getResponse()->setHeader('HTTP/1.1', '301 Moved Permanently');
                $this->getResponse()->setHeader('Location', Mage::getBaseUrl());
            }
        }
    }

    public function constructUrl($urlSplits)
    {
        $url = Mage::getBaseUrl();
        foreach ($urlSplits as $urlSplit) {
            $url .= $urlSplit . '/';
        }

        return $url;
    }

}