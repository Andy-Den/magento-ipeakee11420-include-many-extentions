<?php

use Symfony\Component\Console\Command\Command;

/**
 * Class Balance_EzCommand_Model_MageCommand
 *
 * @author Derek Li
 */
class Balance_EzCommand_Model_MageCommand extends Command
{
    /**
     * @var Balance_Core_Model_Mysql
     */
    protected $mageMysql = null;

    /**
     * @var Balance_Core_Model_Logger
     */
    protected $mageLogger = null;

    /**
     * Get Magento mysql.
     *
     * @return Balance_Core_Model_Mysql
     */
    public function getMageMysql()
    {
        if (!isset($this->mageMysql)) {
            $this->mageMysql = new Balance_Core_Model_Mysql();
        }
        return $this->mageMysql;
    }

    /**
     * Get Magento logger.
     *
     * @return Balance_Core_Model_Logger
     */
    public function getMageLogger()
    {
        if (!isset($this->mageLogger)) {
            $this->mageLogger = new Balance_Core_Model_Logger('Balance_EzCommand.log');
        }
        return $this->mageLogger;
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->process($input, new Balance_EzCommand_Model_Output($output));
    }

    /**
     * Process the command.
     *
     * @param InputInterface $input
     * @param Balance_EzCommand_Model_Output $output
     */
    protected function process(InputInterface $input, Balance_EzCommand_Model_Output $output)
    {

    }
}