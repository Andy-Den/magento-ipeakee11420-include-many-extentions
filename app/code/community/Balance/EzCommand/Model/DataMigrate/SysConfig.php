<?php

class Balance_EzCommand_Model_DataMigrate_SysConfig extends Balance_EzCommand_Model_DataMigrate_Abstract
{
    /**
     * For cms pages, there is no real 'merge' but always 'replace'.
     *
     * @param array $fromDbParams
     * @param array $toDbParams
     * @return mixed
     */
    public function merge(array $fromDbParams, array $toDbParams)
    {
        $mageMysql = $this->getMageMysql();
        $output = $this->getOutput();
        $fDb = $mageMysql->connectTo($fromDbParams);
        $tDb = $mageMysql->connectTo($toDbParams);
        $coreConfigTableName = $mageMysql->getTableName('core/config_data');
        $select = $fDb->select()->from($coreConfigTableName);
        $fConfigs = $fDb->fetchAll($select);
        $output->message(sprintf('There are %s records to migrate.', count($fConfigs)));
        /**
         * @todo 1) Fetch data from from-database page by page.
         *       2) Combine queries into one to query all at once.
         */
        foreach ($fConfigs as $c) {
            $sql = "INSERT INTO `core_config_data` (`scope`, `scope_id`, `path`, `value`)
                    VALUES (\"".$c['scope']."\",\"".$c['scope_id']."\",\"".addslashes($c['path'])."\",\"".addslashes($c['value'])."\")
                    ON DUPLICATE KEY UPDATE `value`=VALUES(`value`);";
            $tDb->query($sql);
        }
    }

    /**
     * Migrate Magento cms pages from one db to another.
     *
     * @param array $fromDbParams
     * @param array $toDbParams
     * @return mixed
     */
    public function replace(array $fromDbParams, array $toDbParams)
    {
        /**
         * @todo Implement 'r' (replace) mode.
         */
        $this->getOutput()->info('[TODO] implement replace mode');
    }
}