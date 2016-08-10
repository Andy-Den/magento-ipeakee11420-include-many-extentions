<?php

/**
 * Milandirect
 *
 * @category  Milandirect
 * @package   Milandirect_SimpleConfigurable
 * @copyright 2016 Balance Internet
 */
class Milandirect_SimpleConfigurable_Model_Resource_Configurable extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * _construct
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('simpleconfigurable/configurable', 'entity_id,store_id');
    }
}
