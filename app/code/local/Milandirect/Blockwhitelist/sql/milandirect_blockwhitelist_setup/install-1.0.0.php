<?php
/**
 *  This setup adds block types to the permission_block table to allow blocks to display in the frontend
 */
$installer = $this;
$installer->startSetup();

$installer->getConnection()->insertMultiple(
    $installer->getTable('admin/permission_block'),
    array(
        array('block_name' => 'cms/block', 'is_allowed' => 1)
    )
);
