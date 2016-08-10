<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Balance_EzCommand_Command_TestSoapAPIV2
 * Test Magento SOAP API V2 connectivity.
 * Sample:
 *
 *     bin/magecli balance_ezcommand:test_soap_api_v2 -u SOAP_API_USERNAME -k SOAP_API_KEY -d DOMAIN_NAME_OF_THE_WEBSITE
 *
 * @author Derek Li
 */
class Balance_EzCommand_Command_TestSoapAPIV2 extends Command
{
    /**
     * Configure the command.
     */
    public function configure()
    {
        $this
            ->setName('balance_ezcommand:test_soap_api_v2')
            ->setDescription('Test Magento SOAP API.')
            ->addOption(
                'username',
                'u',
                InputOption::VALUE_REQUIRED,
                'The soap api username?'
            )
            ->addOption(
                'key',
                'k',
                InputOption::VALUE_REQUIRED,
                'The soap api key?'
            )
            ->addOption(
                'api',
                'a',
                InputOption::VALUE_REQUIRED,
                'Which API, V2 or V2?',
                'V2'
            )
            ->addOption(
                'domain',
                'd',
                InputOption::VALUE_REQUIRED,
                'Which website (domain name is needed) to test?'
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
        $username = $input->getOption('username');
        if (empty($username)) {
            $output->writeln('<error>Please provide the soap api username.</error>');
            return;
        }
        $key = $input->getOption('key');
        if (empty($key)) {
            $output->writeln('<error>Please provide the soap api key.</error>');
            return;
        }
        $domain = $input->getOption('domain');
        if (empty($domain)) {
            $output->writeln('<error>Please provide the domain name.</error>');
            return;
        }
        ini_set("soap.wsdl_cache_enabled", "0");
        $api = $input->getOption('api');
        if ($api == 'V2') {
            $apiUri = 'index.php/api/v2_soap/index?wsdl';
        } else {
            $apiUri = 'index.php/api?wsdl';
        }
        $soapEndpoint = sprintf('%s/%s', rtrim($domain, '/'), $apiUri);
        $client = new SoapClient($soapEndpoint, array('trace' => true));
        try {
            $sessionId = $client->login($username, $key);
            if (empty($sessionId)) {
                $output->writeln('Soap service login failed.');
                return;
            }
        } catch (Exception $e) {
            print($client->__getLastResponse());
        }
        $output->writeln(sprintf('<info>Here is the SOAP endpoint [%s].</info>', $soapEndpoint));
        $output->writeln(sprintf('<info>Soap service login succeed [session id: %s].</info>', $sessionId));
    }
}