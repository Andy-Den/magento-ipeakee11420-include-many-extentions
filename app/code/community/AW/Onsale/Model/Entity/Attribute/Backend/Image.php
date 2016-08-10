<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Onsale
 * @version    2.5.4
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Onsale_Model_Entity_Attribute_Backend_Image extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{

    public function beforeSave($object)
    {
        $value = $object->getData($this->getAttribute()->getName());

        if (is_array($value) && !empty($value['delete'])) {
            $object->setData($this->getAttribute()->getName(), '');
            $this->getAttribute()->getEntity()
                ->saveAttribute($object, $this->getAttribute()->getName());
            return;
        }

        $path = Mage::getBaseDir('media') . DS . 'onsale' . DS . 'uploaded' . DS;

        try {
            $uploader = new Varien_File_Uploader($this->getAttribute()->getName());
            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
            $uploader->setAllowRenameFiles(true);
            $uploader->save($path);

            $object->setData($this->getAttribute()->getName(), $uploader->getUploadedFileName());
            $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getName());
        } catch (Exception $e) {
            return;
        }
    }

    public static function getUploadDirName()
    {
        return Mage::getBaseDir('media') . DS . 'onsale' . DS . 'uploaded' . DS;
    }

    public static function getAllowedImgExt()
    {
        return array('jpg', 'jpeg', 'gif', 'png');
    }

}
