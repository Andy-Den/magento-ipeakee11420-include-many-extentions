<?php

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Interface Balance_EzCommand_Model_DataMigrate_Interface
 *
 * @author Derek Li
 */
interface Balance_EzCommand_Model_DataMigrate_Interface
{
    /**
     * Migrate data from one database to another database.
     *
     * @param array $fromDbParams The 'from' database parameters.
     * @param array $toDbParams The 'to' database parameters.
     * @return mixed
     */
    public function migrate(array $fromDbParams, array $toDbParams);
}