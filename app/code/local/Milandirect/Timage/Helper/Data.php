<?php

/**
 * Rewrite Helper to fix problem on default image on skin
 *
 * @category  Milandirect
 * @package   Milandirect_Timage
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2015 Balance
 */
class Milandirect_Timage_Helper_Data extends Technooze_Timage_Helper_Data
{
    /**
     * Get image path
     *
     * @param string $img image url
     * @return void
     */
    public function imagePath($img = '')
    {
        if ($img == $this->placeHolder) {
            $unSecureSkin = Mage::getStoreConfig('web/unsecure/base_skin_url');
            $secureSkin = Mage::getStoreConfig('web/secure/base_skin_url');
            $img = str_replace(array(
                $unSecureSkin, // unsecure media url
                $secureSkin, // secure media url
                str_replace(array('http:', 'https:'), '', $unSecureSkin), // unsecure skin url without https?
                str_replace(array('http:', 'https:'), '', $secureSkin), // secure skin url without https?
            ), '', $img);
            $img = trim(str_replace('/', DS, $img), DS);
            $this->img = BP . DS . 'skin' . DS . $img;
        } else {
            $unSecureMedia = Mage::getStoreConfig('web/unsecure/base_media_url');
            $secureSkin = Mage::getStoreConfig('web/secure/base_media_url');
            $img = str_replace(array(
                $unSecureMedia, // unsecure media url
                $secureSkin, // secure media url
                str_replace(array('http:', 'https:'), '', $unSecureMedia), // unsecure media url without https?
                str_replace(array('http:', 'https:'), '', $secureSkin), // secure media url without https?
            ), '', $img);
            $img = trim(str_replace('/', DS, $img), DS);
            $this->img = BP . DS . 'media' . DS . $img;
        }
        
        /**
         * First check this file on FS
         * If it doesn't exist - try to download it from DB
         */
        $fileName = str_replace('media' . DS, '', $img);
        if (!file_exists($fileName)) {
            Mage::helper('core/file_storage_database')->saveFileToFilesystem($fileName);
        }
        if ((!file_exists($this->img) || !is_file($this->img)) && !empty($this->placeHolder)) {
            $this->imagePath($this->placeHolder);
            $this->placeHolder = false;
        }
    }
}