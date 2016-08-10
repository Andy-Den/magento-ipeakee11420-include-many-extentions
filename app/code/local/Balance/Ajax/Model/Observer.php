<?php
/**
 * Balance ajax price extension for Magento
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
 * @copyright  Copyright (C) 2012 Balance Internet (http://www.balanceinternet.com.au)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Short description of the class
 *
 * Long description of the class (if any...)
 *
 * @category   Balance
 * @package    Balance_Ajax
 * @subpackage Model
 * @author     Richard Cai <richard@balanceinternet.com.au>
 */
class Balance_Ajax_Model_Observer extends Mage_Core_Model_Abstract
{
    /**
     * process ajax request
     *
     * @param Varien_Event_Observer $event
     */
    public function processAjaxRequest(Varien_Event_Observer $observer)
    {
        $request = $observer->getEvent()->getRequest();
        $response = $observer->getEvent()->getResponse();

        if (Mage::getStoreConfigFlag('balance_ajax/settings/include_form_key')) {
            $formKey = Mage::getSingleton('core/session')->getFormKey();
            if (!empty($formKey)) {
                $response->setData('real_form_key', $formKey);
            }
        }
        $params = $request->getParams();
        $ajax = Mage::getModel('ajax/ajax');
        foreach ($params as $param) {
            if (is_array($param)) {
                $method = 'get' . str_replace(' ', '', ucwords(str_replace(array('_', '.'), ' ', $param['name'])));

                $block = $ajax->$method($param);

                if (empty($block)) {
                    continue;
                }

                $html = is_string($block) ? $block : $block->toHtml();

                $response->setData($param['name'], $html);
            }
        }

        //add form key for 1.13+,


    }

    /**
     * process ajax response
     *
     * @param Varien_Event_Observer $event
     */
    public function processAjaxResponse(Varien_Event_Observer $event)
    {

    }

    /**
     *
     * @param Varien_Event_Observer $observer
     */
    public function initMessageFlag(Varien_Event_Observer $observer)
    {
        $object = $observer->getObject();
        $clear = $object->getData('flag');

        $object->setData(
            'flag',
            $clear
            && (Mage::helper('ajax')->isAjax() || Mage::helper('ajax')->shouldSkip()
                || Mage::app()->getStore()->isAdmin())
        );

        return $this;
    }

    public function renderAjaxBlock($observer)
    {
        if (Mage::app()->getStore()->isAdmin() || Mage::helper('ajax')->shouldSkip()) {
            return;
        }

        $block = $observer->getBlock();
        $transport = $observer->getTransport();

        //Mage::log(get_class($block).','.$block->getNameInLayout());

        $ajax = $block->getData('ajax');

        if (empty($ajax)) {
            return;
        }

        $data = $block->getData('backup');

        if (is_array($data) && !empty($data) && !Mage::helper('ajax')->isAjax()) {

            //if ('ajax/block.phtml' == $block->getTemplate()) return;

            $html = Mage::app()->getLayout()->createBlock('core/template', $data['name'] . '_ajax')
                ->setTemplate('ajax/block.phtml')
                ->setData('backup', $data)
                ->setData('ajax_sort', $block->getData('ajax_sort'))
                ->setData('nodiv', $block->getData('nodiv'))
                ->toHtml();
            $transport->setHtml($html);
        }

        return;
    }
}
