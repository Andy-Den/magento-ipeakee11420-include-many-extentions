<?php

/**
 * Catalog product media gallery attribute backend resource
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Milandirect_Catalog_Model_Resource_Product_Attribute_Backend_Media extends Mage_Catalog_Model_Resource_Product_Attribute_Backend_Media
{
    const GALLERY_TABLE       = 'catalog/product_attribute_media_gallery';
    const GALLERY_VALUE_TABLE = 'catalog/product_attribute_media_gallery_value';
    const GALLERY_IMAGE_TABLE = 'catalog/product_attribute_media_gallery_image';

    protected $_eventPrefix = 'catalog_product_attribute_backend_media';

    private $_attributeId = null;


    /**
     * Load gallery images for product using reusable select method
     *
     * @param Mage_Catalog_Model_Product                         $product product
     * @param Mage_Catalog_Model_Product_Attribute_Backend_Media $object  object
     * @return array
     */
    public function loadGallery($product, $object)
    {
        $eventObjectWrapper = new Varien_Object(
            array(
                'product' => $product,
                'backend_attribute' => $object
            )
        );
        Mage::dispatchEvent(
            $this->_eventPrefix . '_load_gallery_before',
            array('event_object_wrapper' => $eventObjectWrapper)
        );

        if ($eventObjectWrapper->hasProductIdsOverride()) {
            $productIds = $eventObjectWrapper->getProductIdsOverride();
        } else {
            $productIds = array($product->getId());
        }

        $select = $this->_getLoadGallerySelect($productIds, $product->getStoreId(), $object->getAttribute()->getId());

        $adapter = $this->_getReadAdapter();
        $result = $adapter->fetchAll($select);
        if ($product->getTypeId() != 'configurable') {
            $this->_removeDuplicates($result);
        }
        return $result;
    }

    /**
     * Remove duplicates
     *
     * @param array $result result
     * @return Mage_Catalog_Model_Resource_Product_Attribute_Backend_Media
     */
    protected function _removeDuplicates(&$result)
    {
        $fileToId = array();

        foreach (array_keys($result) as $index) {
            if (!isset($fileToId[$result[$index]['file']])) {
                $fileToId[$result[$index]['file']] = $result[$index]['value_id'];
            } elseif ($fileToId[$result[$index]['file']] != $result[$index]['value_id']) {
                $this->deleteGallery($result[$index]['value_id']);
                unset($result[$index]);
            }
        }

        $result = array_values($result);
        return $this;
    }

    /**
     * Get select to retrieve media gallery images
     * for given product IDs.
     *
     * @param array $productIds  productIds
     * @param int   $storeId     storeId
     * @param int   $attributeId attributeId
     * @return Varien_Db_Select
     */
    protected function _getLoadGallerySelect(array $productIds, $storeId, $attributeId)
    {
        $adapter = $this->_getReadAdapter();

        $positionCheckSql = $adapter->getCheckSql('value.position IS NULL', 'default_value.position', 'value.position');

        // Select gallery images for product
        $select = $adapter->select()
            ->from(
                array('main'=>$this->getMainTable()),
                array('value_id', 'value AS file', 'product_id' => 'entity_id')
            )
            ->joinLeft(
                array('value' => $this->getTable(self::GALLERY_VALUE_TABLE)),
                $adapter->quoteInto('main.value_id = value.value_id AND value.store_id = ?', (int)$storeId),
                array('label','position','disabled')
            )
            ->joinLeft( // Joining default values
                array('default_value' => $this->getTable(self::GALLERY_VALUE_TABLE)),
                'main.value_id = default_value.value_id AND default_value.store_id = 0',
                array(
                    'label_default' => 'label',
                    'position_default' => 'position',
                    'disabled_default' => 'disabled'
                )
            )
            ->where('main.attribute_id = ?', $attributeId)
            ->where('main.entity_id in (?)', $productIds)
            ->order($positionCheckSql . ' ' . Varien_Db_Select::SQL_ASC);

        return $select;
    }

    /**
     * Get attribute ID
     *
     * @return int
     */
    protected function _getAttributeId()
    {
        if (is_null($this->_attributeId)) {
            $attribute = Mage::getModel('eav/entity_attribute')
                ->loadByCode(Mage_Catalog_Model_Product::ENTITY, 'media_gallery');

            $this->_attributeId = $attribute->getId();
        }
        return $this->_attributeId;
    }
}
