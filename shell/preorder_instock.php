<?php

require_once 'abstract.php';

class Mage_Shell_Preorder_Instock extends Mage_Shell_Abstract {

    /**
     * Run script
     * Schecule as: 
     *    0 1 * * *   php preorder_instock.php | mail -s "MilanDirect preorder-to-instock" nick@balanceinternet.com.au,rodger@balanceinternet.com.au
     */
    public function run() {
        try {
            $todayDate = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
            
            $products = Mage::getModel('catalog/product')->getCollection()->addAttributeToFilter('preorder_calender', array('date' => true, 'to' => $todayDate));
            $emailContent = '';
            $emailContent .= $todayDate."\n";
            echo 'Preorder-InStock process started @ '.$todayDate.' :'."\n\n";
            echo 'Name'."\t".'SKU'."\n\n";

            // Write into CSV
            $io = new Varien_Io_File();
            $path = Mage::getBaseDir('var') . DS . 'export' . DS;
            $name = md5(microtime());
            $file = $path . DS . $name . '.csv';
            $io->setAllowCreateFolders(true);
            $io->open(array('path' => $path));
            $io->streamOpen($file, 'w+');
            $io->streamLock(true);

            $header = array("Name","SKU");
            $io->streamWriteCsv($header);

            foreach ($products as $product) {
                $product->load();
                $data = array();
                $data[] = $product->getName();
                $data[] = $product->getSku();
                $io->streamWriteCsv($data);
                $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
                $stock->setData('is_in_stock', 1);
                $stock->save();

                $product->setData('preorder_calender', null);
                $product->setData('custom_stock_status', 14);

                $product->save();
                $output = $product->getName()."\t".$product->getSku();
                echo $output."\n";
            }

            // Send email with configuration in System > Configuration > Preoder Instock Updates > Receivers
            $emailTemplate  = Mage::getModel('core/email_template')
                ->loadDefault('preoder_stock_email');

            $emailTemplate->setSenderName('Milan Direct Administrator');
            $emailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email'));
            $emailTemplate->setTemplateSubject('Preorder-InStock process started @ '.$todayDate);

            $emailTemplateVariables = array();
            $emailTemplateVariables['message'] = 'Preorder-InStock process finished with '.$products->count().' updated';
            $processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);

            // Add attachment
            $emailTemplate->getMail()->createAttachment(
                file_get_contents($file),
                Zend_Mime::TYPE_OCTETSTREAM,
                Zend_Mime::DISPOSITION_ATTACHMENT,
                Zend_Mime::ENCODING_BASE64,
                'preorder_stock_report.csv'
            );

            $names = array();
            $receivers = explode(",", Mage::getStoreConfig('notification/general/email'));

            foreach($receivers as $receiver)
            {
                $names[] = 'Milan Direct Staff';
            }

            $emailTemplate->send($receivers,$names, $emailTemplateVariables);

            echo 'Preorder-InStock process finished with '.$products->count().' updated'."\n";
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::log($e->getMessage(), 1, 'preorder_instock.log');
        }
    }

}

$shell = new Mage_Shell_Preorder_Instock();
$shell->run();