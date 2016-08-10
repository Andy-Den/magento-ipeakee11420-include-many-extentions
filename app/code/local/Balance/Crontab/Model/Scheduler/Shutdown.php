<?php
/**
 * Check magento status extension for Magento
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
 * the Balance Crontab module to newer versions in the future.
 * If you wish to customize the Balance Crontab module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Balance
 * @package    Balance_Crontab
 * @copyright  Copyright (C) 2013 Balance Internet (http://www.balanceinternet.com.au)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Short description of the class
 *
 * Long description of the class (if any...)
 *
 * @category   Balance
 * @package    Balance_Crontab
 * @subpackage Model
 * @author     Richard Cai <richard@balanceinternet.com.au>
 */
class Balance_Crontab_Model_Scheduler_Shutdown
{
    protected $_callbacks; // array to store user callbacks

    protected $_mode;

    const REGISTRY_KEY = 'crontab/sheduler/shutdown';

    const GENERAL_MODE = 'GENERAL';

    public function __construct() {
        $this->_callbacks = array();
        $this->_mode = self::GENERAL_MODE;
        $this->_callbacks[$this->_mode] = array();
        register_shutdown_function(array($this, 'callRegisteredShutdown'));
    }

    public function setMode($mode)
    {
        $this->_mode = $mode;
        return $this;
    }

    public function registerShutdownEvent() {
        $callback = func_get_args();
        if (empty($callback)) {
            trigger_error('No callback passed to '.__FUNCTION__.' method', E_USER_ERROR);
            return false;
        }
        if (!is_callable($callback[1])) {
            trigger_error('Invalid callback passed to the '.__FUNCTION__.' method', E_USER_ERROR);
            return false;
        }
        if(!is_array($this->_callbacks[$callback[0]])){
            $this->_callbacks[$callback[0]] = array();
        }
        $this->_callbacks[$callback[0]][] = $callback[1];
        return true;
    }

    public function callRegisteredShutdown() {
        $callbacks = array_reverse($this->_callbacks);
        foreach ($callbacks as $mode => $arguments) {
            $allowed_modes = $this->_getAllowedModes();
            if(in_array($mode, $allowed_modes)){
                $callback = array_shift($arguments);
                call_user_func_array($callback, $arguments);
            }
        }
    }

    /**
     * get allowed modes to execute shutdown functions
     */
    protected function _getAllowedModes()
    {
        return array_unique(array($this->_mode, self::GENERAL_MODE));
    }
}
