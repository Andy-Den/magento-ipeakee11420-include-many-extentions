# \\Ez\\MageCli

### Test

* Unit test.

    ```
    ./dev test
    ```

### To create your own commands.

This package is based on [Symfony Console](http://symfony.com/doc/current/components/console/introduction.html)
Every command should extend Symfony command.
There are 2 ways to register a command:

1. Normal psr0 commands. In any of the directories under root: 'Test.php'

~~~~
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;

    class Ez_MageCli_Command_Test extends Command
    {
        public function configure()
        {
            $this
                ->setName('shell:command_test')
                ->setDescription('Test Ez MageCli command - PSR0.')
                ->addOption(
                    'db',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'What is the id of the database where you want to fetch from?'
                )
                ->addOption(
                    'day',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'What is the day time (Ymd)?'
                )
            ;
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $output->writeln('Test Ez MageCli command execute PSR0.');
        }
    }
~~~~

2. Magento extension command. Inside the extension, under 'Command' folder: 'Test.php'

~~~~
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;

    class Phoenix_Moneybookers_Command_Test extends Command
    {
        public function configure()
        {
            $this
                ->setName('moneybookers:command_test')
                ->setDescription('Test Ez MageCli command.')
                ->addOption(
                    'db',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'What is the id of the database where you want to fetch from?'
                )
                ->addOption(
                    'day',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'What is the day time (Ymd)?'
                )
            ;
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $output->writeln('Test Ez MageCli command execute.');
        }
    }
~~~~