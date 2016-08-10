<?php

/**
 * Class Balance_Core_Model_Mysql
 *
 * @author Derek Li
 */
class Balance_Core_Model_Mysql
{
    /**
     * @return Magento_Db_Adapter_Pdo_Mysql
     */
    public function write()
    {
        return $this->connect('core_write');
    }

    /**
     * @return Magento_Db_Adapter_Pdo_Mysql
     */
    public function read()
    {
        return $this->connect('core_read');
    }

    /**
     * @param string $name OPTIONAL The name of the db adapter.
     * @return Magento_Db_Adapter_Pdo_Mysql
     */
    public function connect($name = 'core_write')
    {
        return Mage::getSingleton('core/resource')->getConnection($name);
    }

    /**
     * Get the table name.
     *
     * @param string $tableKey The key for the table set in config.xml.
     * @return mixed
     */
    public function getTableName($tableKey)
    {
        return Mage::getSingleton('core/resource')->getTableName($tableKey);
    }

    /**
     * Get database adapter based on the given database parameters.
     *
     * @param array $params
     * @return Zend_Db_Adapter_Abstract
     * @throws Zend_Db_Exception
     */
    public function connectTo(array $params)
    {
        return \Zend_Db::factory('Pdo_Mysql', array(
            'host'     => $params['host'],
            'port' => $params['port'],
            'username' => $params['username'],
            'password' => $params['password'],
            'dbname'   => $params['dbname']
        ));
    }
}