<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */

class Amasty_SeoToolKit_Model_Observer
{
	public function redirect301()
	{
        if (Mage::app()->getStore()->isAdmin())
            return;

		$request = Mage::app()->getRequest();

		if (! Mage::isInstalled()
			|| $request->getPost()
			|| strtolower($request->getMethod()) == 'post'
			|| ! Mage::getStoreConfig('amseotoolkit/general/home_redirect')
		) {
			return;
		}

		$baseUrl = Mage::getBaseUrl(
			Mage_Core_Model_Store::URL_TYPE_WEB,
			Mage::app()->getStore()->isCurrentlySecure()
		);

		if (! $baseUrl) {
			return;
		}

		$requestPath = $request->getRequestUri();
		$params      = preg_split('/^.+?\?/', $request->getRequestUri());
		$baseUrl 	.= isset($params[1]) ? '?' . $params[1] : '';

		$redirectUrls = array(
			'',
			'/cms',
			'/cms/',
			'/cms/index',
			'/cms/index/',
			'/index.php',
			'/index.php/',
			'/home',
			'/home/',
		);

		if (!is_null($requestPath) && in_array($requestPath, $redirectUrls)) {
			Mage::app()->getFrontController()->getResponse()
				->setRedirect($baseUrl, 301)
				->sendResponse();

			exit;
		}
	}
}
