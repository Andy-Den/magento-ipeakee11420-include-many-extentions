<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
//use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Balance_EzCommand_Command_Maintain
 * Turn on/off maintenance mode.
 * Sample:
 *
 *     bin/magecli balance_ezcommand:maintain on
 *
 * @author Derek Li
 */
class Balance_EzCommand_Command_Maintain extends Command
{
    /**
     * Configure the command.
     */
    public function configure()
    {
        $this
            ->setName('balance_ezcommand:maintain')
            ->setDescription('Test Magento SOAP API.')
            ->addArgument(
                'action',
                InputArgument::REQUIRED,
                'Action to take ("on" or "off")?'
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
        $flagDist = Mage::getBaseDir().'/balance/maintenance/maintenance.flag.dist';
        if (!file_exists($flagDist)) {
            $output->writeln(sprintf("<error>Maintenance flag dist file [maintenance.flag.dist] not found. No maintenance mode available.<error>"));
            return;
        }
        $action = $input->getArgument('action');
        $flag = Mage::getBaseDir().'/balance/maintenance/maintenance.flag';
        if ($action == 'on') {
            copy($flagDist, $flag);
            $output->writeln(sprintf('Maintenance on.', $flag));
        } elseif ($action == 'off') {
            if (!file_exists($flag)) {
                $output->writeln(sprintf('Maintenance flag [%s] is not found. Maintenance off.', $flag));
                return;
            }
            unlink($flag);
            $output->writeln(sprintf('Maintenance flag [%s] has been removed. Maintenance off.', $flag));
        } else {
            $output->writeln(sprintf('The given action [%s] is not supported. It should be either "on" of "off".', $action));
        }
    }
}