<?php

use Symfony\Component\Console\Command\Command;
//use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
//use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class Ez_MageCli_Command_SyncDataFeedImages
 *
 *     bin/magecli shell:sync_data_feed_images
 *
 * @author Derek Li
 */
class Ez_MageCli_Command_SyncDataFeedImages extends Command
{
    /**
     * Configure the command.
     */
    public function configure()
    {
        $this
            ->setName('shell:sync_data_feed_images')
            ->setDescription('Copy product images into media/cleanimage.');
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
        /**
         * @var $mySql Balance_Core_Model_Mysql
         */
        $mySql = Mage::getModel('balance_core/mysql');
        $db = $mySql->connect();
        $productImages = $db->fetchAll(
            "SELECT
                entity_id, value AS image, sku
            FROM
                (SELECT
                    pg.*, p.sku
                FROM
                    catalog_product_entity_media_gallery AS pg
                LEFT JOIN catalog_product_entity AS p ON pg.entity_id = p.entity_id
                WHERE
                    value != '/'
                ORDER BY value ASC) AS sorted
            GROUP BY entity_id;"
        );
        $mediaDir = Mage::getBaseDir('media');
        $cleanImageDir = $mediaDir.DS.'cleanimage';
        $productImageDir = $mediaDir.DS.'catalog'.DS.'product';
        $numOfCopied = 0;
        foreach ($productImages as $pImage) {
            $sku = $pImage['sku'];
            $cleanImage = preg_replace('/^(UK-)?(.+)$/', '$2', $sku);
            $cleanImageFile = $cleanImageDir.DS.$cleanImage.'.jpg';
            $productImageFile = $productImageDir.$pImage['image'];
            // If the product image exists on the server, copy it over to media/cleanimage
            if (file_exists($productImageFile) &&
                !file_exists($cleanImageFile)
            ) {
                copy($productImageFile, $cleanImageFile);
                $output->writeln(sprintf(
                    'copied image for product [%s] from [%s] to [%s]',
                    $sku,
                    $productImageFile,
                    $cleanImageFile
                ));
                $numOfCopied++;
            }
        }
        $output->writeln(sprintf('Totally copied [%s] images', $numOfCopied));
    }
}