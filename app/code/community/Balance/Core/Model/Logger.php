<?php

/**
 * Class Balance_Core_Model_Logger
 *
 * @author Derek Li
 */
class Balance_Core_Model_Logger
{
    /**
     * The log filename (no path since it will always be var/log).
     *
     * @var string
     */
    protected $_filename = 'balance-core.log';

    /**
     * Balance_Core_Model_Logger constructor.
     *
     * @param string $filename Optional The log filename (no path since it will always be var/log).
     */
    public function __construct($filename = null)
    {
        if (isset($filename)) {
            $this->_filename = $filename;
        }
    }

    /**
     * @param string $filename The log filename (no path since it will always be var/log).
     * @return $this
     */
    public function setFilename($filename)
    {
        $this->_filename = $filename;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getFilename()
    {
        return $this->_filename;
    }

    /**
     * Log in general.
     *
     * @param mixed $msg The message(s) to log.
     * @return $this
     */
    public function log($msg)
    {
        $this->debug($msg);
    }

    /**
     * Log 'debug'.
     *
     * @param mixed $msg The message(s) to log.
     * @return $this
     */
    public function debug($msg)
    {
        Mage::log($msg, Zend_Log::DEBUG, $this->getFilename());
        return $this;
    }

    /**
     * Log info.
     *
     * @param mixed $msg The message(s) to log.
     * @return $this
     */
    public function info($msg)
    {
        Mage::log($msg, Zend_Log::INFO, $this->getFilename());
        return $this;
    }

    /**
     * Log error.
     *
     * @param mixed $msg The message(s) to log.
     * @return $this
     */
    public function error($msg)
    {
        Mage::log($msg, Zend_Log::ERR, $this->getFilename());
        return $this;
    }

    /**
     * Log notice.
     *
     * @param mixed $msg The message(s) to log.
     * @return $this
     */
    public function notice($msg)
    {
        Mage::log($msg, Zend_Log::NOTICE, $this->getFilename());
        return $this;
    }

    /**
     * Log warning.
     *
     * @param mixed $msg The message(s) to log.
     * @return $this
     */
    public function warning($msg)
    {
        Mage::log($msg, Zend_Log::WARN, $this->getFilename());
        return $this;
    }

    /**
     * Log critical.
     *
     * @param mixed $msg The message(s) to log.
     * @return $this
     */
    public function critical($msg)
    {
        Mage::log($msg, Zend_Log::CRIT, $this->getFilename());
        return $this;
    }

    /**
     * Log alert.
     *
     * @param mixed $msg The message(s) to log.
     * @return $this
     */
    public function alert($msg)
    {
        Mage::log($msg, Zend_Log::ALERT, $this->getFilename());
        return $this;
    }

    /**
     * Log emergency.
     *
     * @param mixed $msg The message(s) to log.
     * @return $this
     */
    public function emergency($msg)
    {
        Mage::log($msg, Zend_Log::EMERG, $this->getFilename());
        return $this;
    }
}