<?php

require_once 'abstract.php';

class Mage_Shell_Replacetag extends Mage_Shell_Abstract
{
    public function run()
    {
        if ($this->getArg('run')) {
            $resource = Mage::getSingleton('core/resource');    
            $readConnection = $resource->getConnection('core_read');
            $writeConnection = $resource->getConnection('core_write');
            $query = "SELECT `block_id`, `identifier`, `content` FROM {$resource->getTableName('cms/block')} WHERE `identifier` LIKE '%great_value_deal%'";
            $results = $readConnection->fetchAll($query);
            foreach ($results as $block) {
                $new_content = str_replace(array('<h5>','</h5>','<h2','</h2>','<h5','<h2>'), array('<label>','</label>','<span','</span>','<label','<span>'), $block['content']);                
                Mage::getModel('cms/block')->load($block['block_id'])
                    ->setData('content', $new_content)
                    ->save();
                Mage::log($block['identifier'],null,'replace_cms_block_tag.log');
                echo $block['identifier']."\n";
            }
            echo "Done, check file /var/log/replace_cms_block_tag.log to processed block \n";
        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f replacetag_deal_block.php -- [options]
  run               Replace HTML tag all deal static block
  help              This help

USAGE;
    }
}

$shell = new Mage_Shell_Replacetag();
$shell->run();
