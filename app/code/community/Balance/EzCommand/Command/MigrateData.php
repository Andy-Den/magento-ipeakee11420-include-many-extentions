<?php

//use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Balance_EzCommand_Command_MigrateData.
 * The current Magento database will be used as default for both 'from' and 'to' database.
 * If the 'from' and 'to' are in the same MySQL instance and use same username and password,
 * and also if 'to' is the current Magento database, the only parameter needed is '--f-dbname'.
 *
 * e.g.
 *      php app/console balance_ezcommand:migrate_data --f-host="from your host" --f-user="root" --f-password="GvC1yilfdq3Ajjsd2x" --f-dbname="from_db_name" --t-host="to the host" --t-user="root" --t-password="WAw3damuxx234J8cH" --t-dbname="to_db_name"
 *
 * @package Balance\MageWatchBundle\Command
 * @author Derek Li
 */
class Balance_EzCommand_Command_MigrateData extends Balance_EzCommand_Model_MageCommand
{
    /**
     * Magento db parameters.
     *
     * @var array|null
     */
    protected $magentoDbParams = null;

    /**
     * The 'from' database parameters.
     *
     * @var array|null
     */
    protected $fromDbParams = null;

    /**
     * The 'to' database parameters.
     *
     * @var array|null
     */
    protected $toDbParams = null;

    /**
     * @var array
     */
    protected $dataMigrates = array(
        'cms_block' => 'CmsBlock',
        'cms_page' => 'CmsPage',
        'sys_config' => 'SysConfig'
    );

    /**
     * Config the command.
     */
    protected function configure()
    {
        $this
            ->setName('balance_ezcommand:migrate_data')
            ->setDescription('Migrate magento system configurations from one db to another db.')
            ->addArgument(
                'data-migrate',
                InputArgument::REQUIRED,
                sprintf("The data migrate? (%s)", Zend_Json::encode(array_merge(array_keys($this->dataMigrates), array('all'))))
            )
            ->addOption(
                'mode',
                'm',
                InputArgument::OPTIONAL,
                'What mode to use to migrate data ("m" or "r")?',
                'm'
            )
            ->addOption(
                'f-host',
                null,
                InputOption::VALUE_OPTIONAL,
                'Which database you want to migrate from?'
            )
            ->addOption(
                'f-port',
                null,
                InputArgument::OPTIONAL,
                'What is the port of the database you want to migrate from?',
                3306
            )
            ->addOption(
                'f-dbname',
                null,
                InputOption::VALUE_OPTIONAL,
                'What is the name of the database you want to migrate from?'
            )
            ->addOption(
                'f-user',
                null,
                InputOption::VALUE_OPTIONAL,
                'What is the user of the database you want to migrate from?'
            )
            ->addOption(
                'f-password',
                null,
                InputOption::VALUE_OPTIONAL,
                'What is the password of the database you want to migrate from?'
            )
            ->addOption(
                't-host',
                null,
                InputOption::VALUE_OPTIONAL,
                'Which database you want to migrate to?'
            )
            ->addOption(
                't-dbname',
                null,
                InputOption::VALUE_OPTIONAL,
                'What is the name of the database you want to migrate to?'
            )
            ->addOption(
                't-user',
                null,
                InputOption::VALUE_OPTIONAL,
                'What is the user of the database you want to migrate to?'
            )
            ->addOption(
                't-password',
                null,
                InputOption::VALUE_OPTIONAL,
                'What is the password of the database you want to migrate to?'
            )
            ->addOption(
                't-port',
                null,
                InputOption::VALUE_OPTIONAL,
                'What is the port of the database you want to migrate to?',
                3306
            )
            ->addOption(
                'test',
                null,
                InputOption::VALUE_OPTIONAL,
                'Do you want to run in a test mode?',
                0
            )
        ;
    }

    /**
     * Migrate Magento data from one database to another one.
     *
     * @param InputInterface $input
     * @param Balance_EzCommand_Model_Output $output
     */
    protected function process(InputInterface $input, Balance_EzCommand_Model_Output $output)
    {
        $output->message("Please double check the <bg=yellow;options=bold;fg=black>from</> database:");
        foreach ($this->getFromDbParams($input) as $attr => $val) {
            $output->message(sprintf('  From %s: <info>%s</info>', $attr, $val));
        }
        $output->message("Please double check the <bg=yellow;options=bold;fg=black>to</> database:");
        foreach ($this->getToDbParams($input) as $attr => $val) {
            $output->message(sprintf('  To %s: <info>%s</info>', $attr, $val));
        }
        // Ask for confirmation before migrating the data.
        $dialog = $this->getHelper('dialog');
        if (!$dialog->askConfirmation(
            $output->getOutput(),
            '<question>Are you sure to continue? (Type "y" to continue, or "n" to stop.)</question>',
            false
        )) {
            return;
        }
        $output->message("Start to migrate data...");
        if ($input->getOption('test')) {
            $output->message("You are running in a test mode. The data won't be migrated.");
        } else {
            if (count(array_diff($this->getFromDbParams($input), $this->getToDbParams($input))) === 0) {
                $output->message("<error>Error: the 'from' database is exactly the same as 'to' database.</error>");
                return;
            }
            $dataMigrate = $input->getArgument('data-migrate');
            if ($dataMigrate == 'all') {
                $dataMigrates = array_keys($this->dataMigrates);
            } else {
                $dataMigrates = array($dataMigrate);
            }
            foreach ($dataMigrates as $dm) {
                if (!array_key_exists($dm, $this->dataMigrates)) {
                    $output->message(sprintf("<error>Error: the given data migrate [%s] does not exist.</error>", $dm));
                    return;
                }
                $dataMigrateClass = 'Balance_EzCommand_Model_DataMigrate_'.ucfirst($this->dataMigrates[$dm]);
                if (!class_exists($dataMigrateClass, true)) {
                    $output->message(sprintf("<error>Error: the data migrate class [%s] does not exist.</error>", $dataMigrateClass));
                    return;
                }
                $output->message(sprintf('[%s] starts to migrate.', $dm));
                $dataMigrateInstance = new $dataMigrateClass();
                if ($dataMigrateInstance instanceof Balance_EzCommand_Model_DataMigrate_Abstract) {
                    $dataMigrateInstance
                        ->setOutput($output)
                        ->setMode($input->getOption('mode'))
                        ->setMageMysql($this->getMageMysql())
                        ->setMageLogger($this->getMageLogger());
                }
                $dataMigrateInstance->migrate($this->getFromDbParams($input), $this->getToDbParams($input));
            }
        }
        $output->message("Data migration has been finished.");
    }

    /**
     * Get the 'from' database parameters.
     *
     * @param InputInterface $input
     * @return array|null
     */
    protected function getFromDbParams(InputInterface $input)
    {
        if (!isset($this->fromDbParams)) {
            $this->fromDbParams = array_replace(
                $this->getMagentoDbParams(),
                array_filter(array(
                    'host' => $input->getOption('f-host'),
                    'port' => $input->getOption('f-port'),
                    'username' => $input->getOption('f-user'),
                    'password' => $input->getOption('f-password'),
                    'dbname' => $input->getOption('f-dbname')
                ))
            );
        }
        return $this->fromDbParams;
    }

    /**
     * Get the 'to' database parameters.
     *
     * @param InputInterface $input
     * @return array|null
     */
    protected function getToDbParams(InputInterface $input)
    {
        if (!isset($this->toDbParams)) {
            $this->toDbParams = array_replace(
                $this->getMagentoDbParams(),
                array_filter(array(
                    'host' => $input->getOption('t-host'),
                    'port' => $input->getOption('t-port'),
                    'username' => $input->getOption('t-user'),
                    'password' => $input->getOption('t-password'),
                    'dbname' => $input->getOption('t-dbname')
                ))
            );
        }
        return $this->toDbParams;
    }

    /**
     * Get the current Magento database parameters.
     *
     * @return array
     */
    protected function getMagentoDbParams()
    {
        if (!isset($this->magentoDbParams)) {
            $magentoDbParams = (array)Mage::getConfig()->getResourceConnectionConfig('core_write');
            if (!is_array($magentoDbParams) || count($magentoDbParams) === 0) {
                $this->magentoDbParams = array();
            } else {
                $this->magentoDbParams = array(
                    'host'     => $magentoDbParams['host'],
                    'port'     => 3306,
                    'username' => $magentoDbParams['username'],
                    'password' => $magentoDbParams['password'],
                    'dbname'   => $magentoDbParams['dbname']
                );
            }
        }
        return $this->magentoDbParams;
    }
}