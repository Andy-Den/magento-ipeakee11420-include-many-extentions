<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class Balance_EzCommand_Command_DefragDb
 * To defrag, run (under project root).
 *
 *     bin/magecli balance_ezcommand:defrag_db -d 'your_database'
 *     bin/magecli balance_ezcommand:defrag_db -d 'your_database' silent
 *
 * @author Derek Li
 */
class Balance_EzCommand_Command_DefragDb extends Command
{
    /**
     *  The tables to exclude.
     *
     * @var array
     */
    protected $_excludeTables = array();

    /**
     * Configure the command.
     */
    public function configure()
    {
        $this
            ->setName('balance_ezcommand:defrag_db')
            ->setDescription('Defrag tables.')
            ->addOption(
                'database',
                'd',
                InputOption::VALUE_REQUIRED,
                'Which database to defrag?'
            )
            ->addArgument(
                'mode',
                InputArgument::OPTIONAL,
                'Mode to run?'
            );
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
        // What is the database to defrag from?
        $database = $input->getOption('database');
        if (empty($database)) {
            $output->writeln('<error>Please provide the database name.</error>');
            return;
        }

        $mode = $input->getArgument('mode');
        if ($mode != 'silent') {
            // Double confirm the action since the defragmentation will lock the tables.
            $questionHelper = $this->getHelper('question');
            $question = new ConfirmationQuestion('<question>Please type "DEFRAG DB" to continue.</question>'.PHP_EOL, false, '/^DEFRAG DB$/');
            if (!$questionHelper->ask($input, $output, $question)) {
                $output->writeln('Quit defraging.');
                return;
            }
        }

        // Proceed to defrag tables.
        $output->writeln('Start to defraging tables.');
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $readDb = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $readDb->select();
        // Get all the tables that we need to defrag.
        $select
            ->from(
                'INFORMATION_SCHEMA.TABLES',
                array(
                    'TABLE_NAME',
                    'ENGINE',
                    'FREE' => 'CONCAT(ROUND(DATA_FREE  / ( 1024 * 1024 ), 2), "MB")'
                )
            )
            ->where('TABLE_SCHEMA = ?', $database)
            ->where('DATA_FREE > ?', 0);
        $rows = $readDb->fetchAll($select);
        $numOfTables = count($rows);
        if ($numOfTables === 0) {
            $output->writeln('Defraging is not needed.');
            return;
        } else {
            $output->writeln(sprintf('%s tables need defraging.', $numOfTables));
        }
        $writeDb = Mage::getSingleton('core/resource')->getConnection('core_write');
        $i = 0;
        foreach ($rows as $r) {
            $i++;
            $output->writeln(sprintf('======%d/%d', $i, $numOfTables));
            if (in_array($r['TABLE_NAME'], $this->_excludeTables)) {
                $output->writeln(sprintf('Skip table "%s".', $r['TABLE_NAME']));
                continue;
            }
            $output->writeln(sprintf('Start to defrag table "%s".', $r['TABLE_NAME']));
            $this->_defragTable($r['TABLE_NAME'], $r['ENGINE'], $writeDb, $output);
            $output->writeln(sprintf('Finished defraging table "%s". Free %s.', $r['TABLE_NAME'], $r['FREE']));
        }
    }

    /**
     * Defrag the table.
     *
     * @param string $tableName The name of the table to defrag.
     * @param string $tableEngine The db engine used by this table.
     * @param Magento_Db_Adapter_Pdo_Mysql $db The db connection.
     * @param OutputInterface $output
     */
    protected function _defragTable($tableName, $tableEngine, $db, $output)
    {
        if (strtolower($tableEngine) == 'innodb') {
            $sql = sprintf('ALTER TABLE `%s` ENGINE=INNODB;', $tableName);
        } else {
            $sql = sprintf('OPTIMIZE TABLE `%s`;', $tableName);
        }
        try {
            $db->query($sql);
        } catch (Exception $e) {
            $output->writeln(
                sprintf(
                    'A problem occurred while defraging table "%s": %s',
                    $tableName,
                    $e->getMessage()
                )
            );
        }
    }

    /**
     *
     * @param $msg
     */
    protected function _log($msg)
    {
        Mage::log($msg, null, 'Balance_EzCommand_Command_DefragDb.log');
    }
}