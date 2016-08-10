<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Balance_EzCommand_Command_ResetStaticImageUrls
 * Test Magento SOAP API V2 connectivity.
 * Sample:
 *
 *     bin/magecli balance_ezcommand:reset_static_image_urls -a 1
 *
 * @author Derek Li
 */
class Balance_EzCommand_Command_ResetStaticImageUrls extends Command
{
    /**
     * Configure the command.
     */
    public function configure()
    {
        $this
            ->setName('balance_ezcommand:reset_static_image_urls')
            ->setDescription('Remove domains from image urls used in cms page and cms block.')
            ->addOption(
                'domain',
                'd',
                InputOption::VALUE_REQUIRED,
                'The domain name used in the image urls?'
            )
            ->addOption(
                'all',
                'a',
                InputOption::VALUE_NONE,
                'For all http, https, and naked domains?'
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
        $domain = $input->getOption('domain');
        if (empty($domain)) {
            $output->writeln('<error>Please provide the domain name used in the images.</error>');
            return;
        }
        $domain = rtrim($domain, '/');
        if (!$input->getOption('all')) {
            $this->resetCmsBlocks($domain, $output);
            $this->resetCmsPages($domain, $output);
            return;
        }
        $schema = 'http://';
        $schemaSecure = 'https://';
        $domain = str_replace($schemaSecure, '', str_replace($schema, '', $domain));
        $domainWithSchema = sprintf('%s%s', $schema, $domain);
        $domainWithSchemaSecure = sprintf('%s%s', $schemaSecure, $domain);
        $this->resetCmsBlocks($domainWithSchemaSecure, $output);
        $this->resetCmsBlocks($domainWithSchema, $output);
        $this->resetCmsBlocks($domain, $output);
        $this->resetCmsPages($domainWithSchemaSecure, $output);
        $this->resetCmsPages($domainWithSchema, $output);
        $this->resetCmsPages($domain, $output);
    }

    /**
     * Reset image urls for cms pages.
     *
     * @param string $domain The domain to use.
     * @param OutputInterface $output
     */
    protected function resetCmsPages($domain, OutputInterface $output)
    {
        $output->writeln(sprintf('############ Start resetting cms page image urls for domain [%s].', $domain));
        $writeDb = Mage::getSingleton('core/resource')->getConnection('write');
        $cmsPageTable = Mage::getSingleton('core/resource')->getTableName('cms/page');
        $select = $writeDb
            ->select()
            ->from($cmsPageTable, array('page_id', 'content'))
            ->where('content like ?', '%'.$domain.'%');
        $rows = $writeDb->fetchAll($select);
        if (count($rows) === 0) {
            $output->writeln('Nothing to reset.');
            return;
        }
        $output->writeln(sprintf('Going to reset [%s] records.', count($rows)));
        foreach ($rows as $r) {
            $writeDb->update(
                $cmsPageTable,
                array(
                    'content' => str_replace($domain, '', $r['content'])
                ),
                "page_id = {$r['page_id']}"
            );
        }
        $output->writeln(sprintf('Retting done for domain [%s].', $domain));
        $output->writeln('');
    }

    /**
     * Reset image urls for cms blocks.
     *
     * @param string $domain The domain to use.
     * @param OutputInterface $output
     */
    protected function resetCmsBlocks($domain, OutputInterface $output)
    {
        $output->writeln(sprintf('############ Start resetting cms block image urls for domain [%s].', $domain));
        $writeDb = Mage::getSingleton('core/resource')->getConnection('write');
        $cmsBlockTable = Mage::getSingleton('core/resource')->getTableName('cms/block');
        $select = $writeDb
            ->select()
            ->from($cmsBlockTable, array('block_id', 'content'))
            ->where('content like ?', '%'.$domain.'%');
        $rows = $writeDb->fetchAll($select);
        if (count($rows) === 0) {
            $output->writeln('Nothing to reset.');
            return;
        }
        $output->writeln(sprintf('Going to reset [%s] records.', count($rows)));
        foreach ($rows as $r) {
            $writeDb->update(
                $cmsBlockTable,
                array(
                    'content' => str_replace($domain, '', $r['content'])
                ),
                "block_id = {$r['block_id']}"
            );
        }
        $output->writeln(sprintf('Retting done for domain [%s].', $domain));
        $output->writeln('');
    }
}