<?php

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Balance_EzCommand_Model_DataMigrate_Abstract
 *
 * @author Derek Li
 */
abstract class Balance_EzCommand_Model_DataMigrate_Abstract implements Balance_EzCommand_Model_DataMigrate_Interface
{
    /**
     * @var Balance_EzCommand_Model_Output
     */
    protected $output = null;

    /**
     * The mode to take. 'm' for merging, 'r' for replacing.
     *
     * @var string
     */
    protected $mode = 'm';

    /**
     * @var Balance_Core_Model_Mysql
     */
    protected $mageMysql = null;

    /**
     * @var Balance_Core_Model_Logger
     */
    protected $mageLogger = null;

    /**
     * @param Balance_EzCommand_Model_Output $output
     *
     * @return $this
     */
    public function setOutput(Balance_EzCommand_Model_Output $output)
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @return Balance_EzCommand_Model_Output
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param string $mode The migration mode.
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param Balance_Core_Model_Mysql $mageMysql
     * @return $this
     */
    public function setMageMysql(Balance_Core_Model_Mysql $mageMysql)
    {
        $this->mageMysql = $mageMysql;
        return $this;
    }

    /**
     * @return Balance_Core_Model_Mysql
     */
    public function getMageMysql()
    {
        return $this->mageMysql;
    }

    /**
     * @param Balance_Core_Model_Logger $mageLogger
     * @return $this
     */
    public function setMageLogger(Balance_Core_Model_Logger $mageLogger)
    {
        $this->mageLogger = $mageLogger;
        return $this;
    }

    /**
     * @return Balance_Core_Model_Logger
     */
    public function getMageLogger()
    {
        return $this->mageLogger;
    }

    /**
     * Migreate data based on the mode.
     *
     * @param array $fromDbParams
     * @param array $toDbParams
     * @return mixed
     */
    public function migrate(array $fromDbParams, array $toDbParams)
    {
        switch ($this->getMode()) {
            case 'm':
                return $this->merge($fromDbParams, $toDbParams);
            case 'r':
                return $this->replace($fromDbParams, $toDbParams);
            default:
                return $this->replace($fromDbParams, $toDbParams);
        }
    }

    /**
     * Merge data from 'from database' to 'to database'.
     * Existing data in 'to database' will be replaced by 'from database',
     * otherwise will be created in 'to database';
     *
     * @param array $fromDbParams
     * @param array $toDbParams
     * @return mixed
     */
    abstract public function merge(array $fromDbParams, array $toDbParams);

    /**
     * Replace the data in 'to database' entirely by data from 'from database'.
     *
     * @param array $fromDbParams
     * @param array $toDbParams
     * @return mixed
     */
    abstract public function replace(array $fromDbParams, array $toDbParams);
}