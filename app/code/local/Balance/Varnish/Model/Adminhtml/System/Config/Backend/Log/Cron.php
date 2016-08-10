<?php

/**
 * Varnish
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the PageCache powered by Varnish License
 * that is bundled with this package in the file LICENSE_VARNISH_CACHE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.phoenix-media.eu/license/license_varnish_cache.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@phoenix-media.eu so we can send you a copy immediately.
 *
 * @category   Balance
 * @package    Balance Varnish
 * @license    http://www.phoenix-media.eu/license/license_varnish_cache.txt
 */
class Balance_Varnish_Model_Adminhtml_System_Config_Backend_Log_Cron
    extends Mage_Core_Model_Config_Data
{
    const CRON_STRING_PATH = 'crontab/jobs/varnish_crawler/schedule/cron_expr';
    const CRON_MODEL_PATH = 'crontab/jobs/varnish_crawler/run/model';

    /* (non-PHPdoc)
     * @see code/core/Mage/Core/Model/Mage_Core_Model_Abstract::_beforeSave()
     */
    protected function _beforeSave()
    {
        $cronExprString = $this->getData('groups/varnish_crawler/fields/cron_expr/value');
        try {
            Mage::getModel('core/config_data')
                ->load(self::CRON_STRING_PATH, 'path')
                ->setValue($cronExprString)
                ->setPath(self::CRON_STRING_PATH)
                ->save();
            Mage::getModel('core/config_data')
                ->load(self::CRON_MODEL_PATH, 'path')
                ->setValue((string)Mage::getConfig()->getNode(self::CRON_MODEL_PATH))
                ->setPath(self::CRON_MODEL_PATH)
                ->save();
        } catch (Exception $e) {
            Mage::throwException(Mage::helper('adminhtml')->__('Unable to save the cron expression.'));
        }
    }
}