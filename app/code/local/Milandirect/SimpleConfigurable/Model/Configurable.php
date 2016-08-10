<?php

/**
 * Milandirect
 *
 * @category  Milandirect
 * @package   Milandirect_SimpleConfigurable
 * @copyright 2016 Balance Internet
 */
class Milandirect_SimpleConfigurable_Model_Configurable extends Mage_Core_Model_Abstract
{
    /**
     * _construct
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('simpleconfigurable/configurable');
    }
}
