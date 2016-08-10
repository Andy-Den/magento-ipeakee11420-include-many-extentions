<?php

namespace Ez\MageCli\Command\Builtin;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
//use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ez\MageCli\Command\BuiltinCommand;

/**
 * Class ClearCache
 * Clear mage cli cache.
 * Sample:
 *
 *     bin/magecli balance_ezcommand:test_soap_api_v2 -u SOAP_API_USERNAME -k SOAP_API_KEY -d DOMAIN_NAME_OF_THE_WEBSITE
 *
 * @author Derek Li
 */
class ClearCache extends BuiltinCommand
{
    /**
     * Configure the command.
     */
    public function configure()
    {
        $this
            ->setName('clear_cache')
            ->setDescription('Clear mage cli cache.');
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
        foreach($this->getFindersOptions() as $finderOptions) {
            if (array_key_exists('options', $finderOptions) &&
                is_array($finderOptions['options']) &&
                array_key_exists('classes_cache_filename', $finderOptions['options'])) {
                $cacheFile = $finderOptions['options']['classes_cache_filename'];
                /**
                 * If everything is right, the cache file should always exist
                 * since it will be generated during the configuration of the console.
                 */
                if (file_exists($cacheFile)) {
                    try {
                        unlink($cacheFile);
                        $output->writeln(sprintf('Cache file [%s] has been deleted.', $cacheFile));
                    } catch (Exception $e) {
                        $output->writeln(sprintf('An error occurred when deleting cache file [%s]: %s', $cacheFile, $e->getMessage()));
                    }
                } else {
                    $output->writeln(sprintf('Cache file [%s] does not exist.', $cacheFile));
                }
            }
        }
        $output->writeln('Mage cli cache cleared');
    }
}