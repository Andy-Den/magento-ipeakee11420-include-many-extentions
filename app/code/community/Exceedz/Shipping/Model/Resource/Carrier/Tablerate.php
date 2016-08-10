<?php
/**
 * Shipping table rates
 * Override Mage_Shipping
 * @category   Exceedz
 * @package    Exceedz_Shipping
 */
class Exceedz_Shipping_Model_Resource_Carrier_Tablerate extends Mage_Shipping_Model_Resource_Carrier_Tablerate
{
	/**
     * Validate row for import and return table rate array or false
     * Error will be add to _importErrors array
     *
     * @param array $row
     * @param int $rowNumber
     * @return array|false
     */
    protected function _getImportRow($row, $rowNumber = 0)
    {
        // validate row
        if (count($row) < 5) {
            $this->_importErrors[] = Mage::helper('shipping')->__('Invalid Table Rates format in the Row #%s',
                $rowNumber);
            return false;
        }

        // strip whitespace from the beginning and end of each row
        foreach ($row as $k => $v) {
            $row[$k] = trim($v);
        }

        // validate country
        if (isset($this->_importIso2Countries[$row[0]])) {
            $countryId = $this->_importIso2Countries[$row[0]];
        } elseif (isset($this->_importIso3Countries[$row[0]])) {
            $countryId = $this->_importIso3Countries[$row[0]];
        } elseif ($row[0] == '*' || $row[0] == '') {
            $countryId = '0';
        } else {
            $this->_importErrors[] = Mage::helper('shipping')->__('Invalid Country "%s" in the Row #%s.',
                $row[0], $rowNumber);
            return false;
        }
        /* Exceed UK - GB fix, UK has two entries in the country table*/
   if ($countryId == 'UK') {
            $countryId = 'GB';
        }
        /* End Exceed*/

        // validate region
        if ($countryId != '0' && isset($this->_importRegions[$countryId][$row[1]])) {
            $regionId = $this->_importRegions[$countryId][$row[1]];
        } elseif ($row[1] == '*' || $row[1] == '') {
            $regionId = 0;
        } else {
            $this->_importErrors[] = Mage::helper('shipping')->__('Invalid Region/State "%s" in the Row #%s.',
                $row[1], $rowNumber);
            return false;
        }


        // detect zip code
        if ($row[2] == '*' || $row[2] == '') {
            $zipCode = '*';
        } else {
            $zipCode = $row[2];
        }

        // validate condition value
        $value = $this->_parseDecimalValue($row[3]);
        if ($value === false) {
            //$this->_importErrors[] = Mage::helper('shipping')->__('Invalid %s "%s" in the Row #%s.',
                //$this->_getConditionFullName($this->_importConditionName), $row[3], $rowNumber);
            //return false;
        }

        // validate weight rate
        $weightRate = $this->_parseDecimalValue($row[4]);
        if ($weightRate === false) {
            $this->_importErrors[] = Mage::helper('shipping')->__('Invalid Shipping Weight Rate "%s" in the Row #%s.',
                $row[4], $rowNumber);
            return false;
        }

		// validate price
        $price = $this->_parseDecimalValue($row[5]);
        if ($price === false) {
            $this->_importErrors[] = Mage::helper('shipping')->__('Invalid Shipping Price "%s" in the Row #%s.',
                $row[5], $rowNumber);
            return false;
        }

		// validate markup
        $markup = $this->_parseDecimalValue($row[6]);
        if ($markup === false) {
            $this->_importErrors[] = Mage::helper('shipping')->__('Invalid Shipping Markup "%s" in the Row #%s.',
                $row[6], $rowNumber);
            return false;
        }

        // protect from duplicate
        $hash = sprintf("%s-%d-%s-%F", $countryId, $regionId, $zipCode, $value);
        if (isset($this->_importUniqueHash[$hash])) {
            $this->_importErrors[] = Mage::helper('shipping')->__('Duplicate Row #%s (Country "%s", Region/State "%s", Zip "%s" and Value "%s").',
                $rowNumber, $row[0], $row[1], $zipCode, $value);
            return false;
        }
        $this->_importUniqueHash[$hash] = true;

        return array(
            $this->_importWebsiteId,    // website_id
            $countryId,                 // dest_country_id
            $regionId,                  // dest_region_id,
            $zipCode,                   // dest_zip
            $this->_importConditionName,// condition_name,
            $value,                     // condition_value
			$weightRate,				// weight rate
            $price,                     // price
			$markup						// markup
        );
    }

    /**
     * Save import data batch
     *
     * @param array $data
     * @return Exceedz_Shipping_Model_Resource_Carrier_Tablerate
     */
    protected function _saveImportData(array $data)
    {
        if (!empty($data)) {
            $columns = array('website_id', 'dest_country_id', 'dest_region_id', 'dest_zip',
                'condition_name', 'condition_value', 'weight_rate', 'price', 'markup');
            $this->_getWriteAdapter()->insertArray($this->getMainTable(), $columns, $data);
            $this->_importedRows += count($data);
        }

        return $this;
    }
}