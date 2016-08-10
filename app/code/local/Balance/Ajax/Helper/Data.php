<?php
/**
 * Deployment script across server cluster extension for Magento
 *
 * Long description of this file (if any...)
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Balance Ajax module to newer versions in the future.
 * If you wish to customize the Balance Ajax module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Balance
 * @package    Balance_Ajax
 * @copyright  Copyright (C) 2012 Balance Internet (http://www.balanceinternet.com.au/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Short description of the class
 *
 * Long description of the class (if any...)
 *
 * @category   Balance
 * @package    Balance_Ajax
 * @subpackage Helper
 * @author     Richard Cai <richard@balanceinternet.com.au>
 */
class Balance_Ajax_Helper_Data extends Mage_Core_Helper_Data
{
    public function canDo()
    {
        return true;
    }

    public function isAjax()
    {
        return Mage::app()->getFrontController()->getRequest()->getModuleName() == 'ajax';
    }

    public function shouldSkip()
    {
        //@todo get controllers from admin
        $skipModules = array('checkout', 'customer', 'onestepcheckout');
        $moduleName = Mage::app()->getRequest()->getModuleName();

        return in_array($moduleName, $skipModules);
    }

    public function getAjaxString($block)
    {
        $data = $block->getData('backup');
        unset($data['id']);

        return http_build_query($data);
    }

    public function getId($block)
    {
        $data = $block->getData('backup');

        return $data['id'];
    }

    public function getAjaxSort($block)
    {
        $ajax_sort = $block->getData('ajax_sort');

        return intval($ajax_sort);
    }
}
