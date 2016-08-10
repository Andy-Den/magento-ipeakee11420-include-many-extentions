<?php

class Balance_EzCommand_Model_DataMigrate_CmsPage extends Balance_EzCommand_Model_DataMigrate_Abstract
{
    /**
     * For cms pages, there is no real 'merge' but always 'replace'.
     *
     * @param array $fromDbParams
     * @param array $toDbParams
     * @return mixed
     */
    public function merge(array $fromDbParams, array $toDbParams)
    {
        /**
         * @todo Implement 'r' (replace) mode.
         */
        $this->getOutput()->info('[TODO] implement merge mode');
    }

    /**
     * Migrate Magento cms pages from one db to another.
     *
     * @param array $fromDbParams
     * @param array $toDbParams
     * @return mixed
     */
    public function replace(array $fromDbParams, array $toDbParams)
    {
//        $mageMysql = $this->getMageMysql();
//        $output = $this->getOutput();
//        $fDb = $mageMysql->connectTo($fromDbParams);
//        $tDb = $mageMysql->connectTo($toDbParams);
        switch (Mage::getEdition()) {
            case Mage::EDITION_COMMUNITY:
                $this->getOutput()->info(sprintf(
                    'Please manually dump and import tables [%s]',
                    Zend_Json::encode(array(
                        $this->getCmsPageTableName(),
                        $this->getCmsPageStoreTableName()
                    ))
                ));
                break;
            case Mage::EDITION_ENTERPRISE:
                $this->getOutput()->info(sprintf(
                    'Please manually dump and import tables [%s]',
                    Zend_Json::encode(array(
                        $this->getCmsPageTableName(),
                        $this->getCmsPageStoreTableName(),
                        $this->getCmsPageRevisionTableName(),
                        $this->getCmsPageVersionTableName()
                    ))
                ));
                break;
            default:
                $this->getOutput()->error(sprintf(
                    'Given Magento edition [%s] is not supported.',
                    Mage::getEdition()
                ));
                break;
        }
    }

    /**
     * @return string
     */
    public function getCmsPageTableName()
    {
        return $this->getMageMysql()->getTableName('cms/page');
    }

    /**
     * @return string
     */
    public function getCmsPageStoreTableName()
    {
        return $this->getMageMysql()->getTableName('cms/page_store');
    }

    /**
     * @return string
     */
    public function getCmsPageRevisionTableName()
    {
        return $this->getMageMysql()->getTableName('enterprise_cms/page_revision');
    }

    /**
     * @return string
     */
    public function getCmsPageVersionTableName()
    {
        return $this->getMageMysql()->getTableName('enterprise_cms/page_version');
    }

    /**
     * Get cms pages indexed by 'identifier'.
     *
     * @param Zend_Db_Adapter_Pdo_Mysql $db
     * @return array
     */
    public function getCmsPages(Zend_Db_Adapter_Pdo_Mysql $db)
    {
        $rows = $db->fetchAll(
            $db
                ->select()
                ->from($this->getCmsPageTableName())
        );
        $cmsPages = array();
        foreach ($rows as $r) {
            $cmsPages[$r['identifier']] = $r;
        }
        return $cmsPages;
    }

    /**
     * Get cms page stores which looks like this:
     * array(
     *      'page_id 1' => array(
     *          'store_id 1',
     *          'store_id 2'
     *      ),
     *      ...
     * )
     *
     * @param Zend_Db_Adapter_Pdo_Mysql $db
     * @return array
     */
    public function getCmsPageStores(Zend_Db_Adapter_Pdo_Mysql $db)
    {
        $rows = $db->fetchAll(
            $db
                ->select()
                ->from($this->getCmsPageStores())
        );
        $cmsPageStores = array();
        foreach ($rows as $r) {
            $cmsPageStores[$r['page_id']][] = $r['store_id'];
        }
        return $cmsPageStores;
    }
}