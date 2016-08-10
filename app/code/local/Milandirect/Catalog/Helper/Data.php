<?php
/**
 * Add product label function
 *
 * @package    Milandirect_Catalog
 * @author     Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2016 Balance
 */
class Milandirect_Catalog_Helper_Data extends Mage_Catalog_Helper_Data
{

    protected $_position = array(
        'free_shipping' => 'TR',
        'sale' => 'TR',
        'sold_out' => 'MC',
        'showroom' => 'TL'
    );

    /**
     * Get position from label
     * @param string $label product sale label
     * @return string
     */
    public function getPosition($label)
    {
        return $this->_position[$label];
    }

    /**
     * Get product label temporary use only one
     * @param varien object $product magento product
     * @return string
     */
    public function getLabel($product)
    {
        if ($stockItem = $product->getStockItem()) {
            if (!$stockItem->getData('is_in_stock')) {
                return 'sold_out';
            }
        } elseif (!$product->isSableAble()) {
            return 'sold_out';
        }

        if ($product->getData('showroom')) {
            return 'showroom';
        }

        if ($product->getData('free_shipping')) {
            return 'free_shipping';
        }

        if ($product->getData('special_price') != '' && $product->getData('special_price') != null) {
            return 'sale';
        }

        return '';
    }

    /**
     * Retrives current image size html tag params
     * @param string $imageType product label type
     * @param string $page      listing page / product page
     * @return string
     */
    public function getImageSizeHtml($imageType, $page = null)
    {
        if ($page == null) {
            $page = 'list';
        }
        $imageFile = Mage::getBaseDir('media').DS.'wysiwyg'.DS.'label'.DS.$page.DS.$imageType.'.png';

        if (file_exists($imageFile)) {
            try {
                list($w, $h) = getimagesize($imageFile);
            } catch (Exception $e) {
                list($w, $h) = array(80, 80);
            }
        } else {
            list($w, $h) = array(80, 80);
        }

        return 'width: ' . $w . 'px; height: ' . $h . 'px;';
    }

    /**
     * Get Image url
     * @param string $imageType product label type
     * @param string $page      listing page / product page
     * @return string
     */
    public function getImageUrl($imageType, $page = null)
    {
        if ($page == null) {
            $page = 'list';
        }
        return Mage::getBaseUrl('media').'/wysiwyg'.'/label/'.$page.'/'.$imageType.'.png';
    }
}
