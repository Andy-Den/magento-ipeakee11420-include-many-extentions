<?php

use Symfony\Component\Console\Command\Command;
//use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
//use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class Ez_MageCli_Command_UpdateCustomOptionsToOptional
 *
 *     bin/magecli shell:update_custom_options_to_optional -t simple
 *
 * @author Derek Li
 */
class Ez_MageCli_Command_UpdateCustomOptionsToOptional extends Command
{
    /**
     * Configure the command.
     */
    public function configure()
    {
        $this
            ->setName('shell:update_custom_options_to_optional')
            ->setDescription('Update product customer options all to optional.')
            ->addOption(
                'type',
                't',
                InputOption::VALUE_OPTIONAL,
                'Product type?'
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
        $productOptionTable = Mage::getSingleton('core/resource')->getTableName('catalog/product_option');
        $writeDb = Mage::getSingleton('core/resource')->getConnection('core_write');
        $productType = $input->getOption('type');

        $output->writeln('Start to update.');
        try {
            if (!empty($productType)) {
                if ($productType == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE ||
                    $productType == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
                ) {
                    $productTable = Mage::getSingleton('core/resource')->getTableName('catalog/product');
                    $writeDb->query(sprintf(
                        'UPDATE `%s` SET `is_require` = 0 WHERE `product_id` IN (SELECT `entity_id` FROM `%s` WHERE `type_id`="%s");',
                        $productOptionTable,
                        $productTable,
                        $productType
                    ));
                    $output->writeln(sprintf('All [%s] product custom options have been updated to optional.', $productType));
                } else {
                    $output->writeln(sprintf('[ERROR] The given product type [%s] is not supported.', $productType));
                }
            } else {
                $writeDb->query(sprintf(
                    'UPDATE `%s` SET `is_require` = 0;',
                    $productOptionTable
                ));
                $output->writeln('All product custom options have been updated to optional.');
            }
        } catch (Exception $e) {
            $output->writeln(sprintf('[ERROR] An error occurred when updating: [%s].', $e->getMessage()));
        }
    }
}