<?php

class Balance_EzCommand_Model_DataMigrate_CmsBlock extends Balance_EzCommand_Model_DataMigrate_Abstract
{
    /**
     * For cms blocks, there is no real 'merge' but always 'replace'.
     *
     * @param array $fromDbParams
     * @param array $toDbParams
     * @return mixed
     */
    public function merge(array $fromDbParams, array $toDbParams)
    {
        $mageMysql = $this->getMageMysql();
        $output = $this->getOutput();
        $fDb = $mageMysql->connectTo($fromDbParams);
        $tDb = $mageMysql->connectTo($toDbParams);
        $fromCmsBlocks = $this->getCmsBlocks($fDb);
        $toCmsBlocks = $this->getCmsBlocks($tDb);
        $fromCmsBlockStores = $this->getCmsBlockStores($fDb);
        $newToCmsBlockStores = array();
        foreach ($fromCmsBlocks as $identifier => $p) {
            $fromBlockId = $p['block_id'];
            unset($p['block_id']);
            // Update the existing block.
            if (array_key_exists($identifier, $toCmsBlocks)) {
                $toBlockId = $toCmsBlocks[$identifier]['block_id'];
                $newBlockId = $toBlockId;
                $output->message(sprintf(
                    '====Replace block [%s] from [%s] to [%s] in to database.',
                    $identifier,
                    $fromBlockId,
                    $newBlockId
                ));
                $tDb->update(
                    $this->getCmsBlockTableName(),
                    $p,
                    $tDb->quoteInto('block_id = ?', $newBlockId)
                );
            } else { // Insert the new blocks that do not exist.
                $output->message(sprintf('+++++Create new block [%s] in to database.', $identifier));
                $tDb->insert(
                    $this->getCmsBlockTableName(),
                    $p
                );
                $newBlockId = $tDb->lastInsertId();
            }
            if (is_array($fromCmsBlockStores[$fromBlockId])) {
                foreach (array_unique($fromCmsBlockStores[$fromBlockId]) as $storeId) {
                    $newToCmsBlockStores[] = array(
                        'block_id' => $newBlockId,
                        'store_id' => $storeId
                    );
                }
            }
        }
        // Refresh cms block store table.
        if (count($newToCmsBlockStores) > 0) {
            $tDb->query(sprintf('TRUNCATE `%s`', $this->getCmsBlockStoreTableName()));
            foreach ($newToCmsBlockStores as $pair) {
                $tDb->insert($this->getCmsBlockStoreTableName(), $pair);
            }
        }
    }

    /**
     * Migrate Magento cms blocks from one db to another.
     *
     * @param array $fromDbParams
     * @param array $toDbParams
     * @return mixed
     */
    public function replace(array $fromDbParams, array $toDbParams)
    {
        /**
         * @todo Implement 'r' (replace) mode.
         */
        $this->getOutput()->info('[TODO] implement replace mode');
    }

    /**
     * @return string
     */
    public function getCmsBlockTableName()
    {
        return $this->getMageMysql()->getTableName('cms/block');
    }

    /**
     * @return string
     */
    public function getCmsBlockStoreTableName()
    {
        return $this->getMageMysql()->getTableName('cms/block_store');
    }

    /**
     * Get cms blocks indexed by 'identifier'.
     *
     * @param Zend_Db_Adapter_Pdo_Mysql $db
     * @return array
     */
    public function getCmsBlocks(Zend_Db_Adapter_Pdo_Mysql $db)
    {
        $rows = $db->fetchAll(
            $db->select()->from($this->getCmsBlockTableName())
        );
        $cmsBlocks = array();
        foreach ($rows as $r) {
            // No 'identifier', not a valid block.
            if (empty($r['identifier'])) {
                continue;
            }
            $cmsBlocks[$r['identifier']] = $r;
        }
        return $cmsBlocks;
    }

    /**
     * Get cms block stores which looks like this:
     * array(
     *      'block_id 1' => array(
     *          'store_id 1',
     *          'store_id 2'
     *      ),
     *      ...
     * )
     *
     * @param Zend_Db_Adapter_Pdo_Mysql $db
     * @return array
     */
    public function getCmsBlockStores(Zend_Db_Adapter_Pdo_Mysql $db)
    {
        $rows = $db->fetchAll(
            $db->select()->from($this->getCmsBlockStoreTableName())
        );
        $cmsBlockStores = array();
        foreach ($rows as $r) {
            $cmsBlockStores[$r['block_id']][] = $r['store_id'];
        }
        return $cmsBlockStores;
    }
}