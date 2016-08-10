<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Dataflow
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Convert csv parser
 *
 * @category   Mage
 * @package    Mage_Dataflow
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Dataflow_Model_Convert_Parser_Csv extends Mage_Dataflow_Model_Convert_Parser_Abstract
{
    protected $_fields;

    protected $_mapfields = array();

    public function parse()
    {
        // fixed for multibyte characters
        setlocale(LC_ALL, Mage::app()->getLocale()->getLocaleCode().'.UTF-8');

        $fDel = $this->getVar('delimiter', ',');
        $fEnc = $this->getVar('enclose', '"');
        if ($fDel == '\t') {
            $fDel = "\t";
        }

        $adapterName   = $this->getVar('adapter', null);
        $adapterMethod = $this->getVar('method', 'saveRow');

        if (!$adapterName || !$adapterMethod) {
            $message = Mage::helper('dataflow')->__('Please declare "adapter" and "method" nodes first.');
            $this->addException($message, Mage_Dataflow_Model_Convert_Exception::FATAL);
            return $this;
        }

        try {
            $adapter = Mage::getModel($adapterName);
        }
        catch (Exception $e) {
            $message = Mage::helper('dataflow')->__('Declared adapter %s was not found.', $adapterName);
            $this->addException($message, Mage_Dataflow_Model_Convert_Exception::FATAL);
            return $this;
        }

        if (!is_callable(array($adapter, $adapterMethod))) {
            $message = Mage::helper('dataflow')->__('Method "%s" not defined in adapter %s.', $adapterMethod, $adapterName);
            $this->addException($message, Mage_Dataflow_Model_Convert_Exception::FATAL);
            return $this;
        }

        $batchModel = $this->getBatchModel();
        $batchIoAdapter = $this->getBatchModel()->getIoAdapter();

        if (Mage::app()->getRequest()->getParam('files')) {
            $file = Mage::app()->getConfig()->getTempVarDir().'/import/'
                . urldecode(Mage::app()->getRequest()->getParam('files'));
            $this->_copy($file);
        }

        $batchIoAdapter->open(false);

        $isFieldNames = $this->getVar('fieldnames', '') == 'true' ? true : false;
        if (!$isFieldNames && is_array($this->getVar('map'))) {
            $fieldNames = $this->getVar('map');
        }
        else {
            $fieldNames = array();
            foreach ($batchIoAdapter->read(true, $fDel, $fEnc) as $v) {
                $fieldNames[$v] = $v;
            }
        }

        $countRows = 0;
        while (($csvData = $batchIoAdapter->read(true, $fDel, $fEnc)) !== false) {
            if (count($csvData) == 1 && $csvData[0] === null) {
                continue;
            }

            $itemData = array();
            $countRows ++; $i = 0;
            foreach ($fieldNames as $field) {
                $itemData[$field] = isset($csvData[$i]) ? $csvData[$i] : null;
                $i ++;
            }

            $batchImportModel = $this->getBatchImportModel()
                ->setId(null)
                ->setBatchId($this->getBatchModel()->getId())
                ->setBatchData($itemData)
                ->setStatus(1)
                ->save();
        }

        $this->addException(Mage::helper('dataflow')->__('Found %d rows.', $countRows));
        $this->addException(Mage::helper('dataflow')->__('Starting %s :: %s', $adapterName, $adapterMethod));

        $batchModel->setParams($this->getVars())
            ->setAdapter($adapterName)
            ->save();

        //$adapter->$adapterMethod();

        return $this;

//        // fix for field mapping
//        if ($mapfields = $this->getProfile()->getDataflowProfile()) {
//            $this->_mapfields = array_values($mapfields['gui_data']['map'][$mapfields['entity_type']]['db']);
//        } // end
//
//        if (!$this->getVar('fieldnames') && !$this->_mapfields) {
//            $this->addException('Please define field mapping', Mage_Dataflow_Model_Convert_Exception::FATAL);
//            return;
//        }
//
//        if ($this->getVar('adapter') && $this->getVar('method')) {
//            $adapter = Mage::getModel($this->getVar('adapter'));
//        }
//
//        $i = 0;
//        while (($line = fgetcsv($fh, null, $fDel, $fEnc)) !== FALSE) {
//            $row = $this->parseRow($i, $line);
//
//            if (!$this->getVar('fieldnames') && $i == 0 && $row) {
//                $i = 1;
//            }
//
//            if ($row) {
//                $loadMethod = $this->getVar('method');
//                $adapter->$loadMethod(compact('i', 'row'));
//            }
//            $i++;
//        }
//
//        return $this;
    }

    public function parseRow($i, $line)
    {
        if (sizeof($line) == 1) return false;

        if (0==$i) {
            if ($this->getVar('fieldnames')) {
                $this->_fields = $line;
                return;
            } else {
                foreach ($line as $j=>$f) {
//                    $this->_fields[$j] = 'column'.($j+1);
                    $this->_fields[$j] = $this->_mapfields[$j];
                }
            }
        }

        $resultRow = array();

        foreach ($this->_fields as $j=>$f) {
            $resultRow[$f] = isset($line[$j]) ? $line[$j] : '';
        }
        return $resultRow;
    }

    /**
     * Read data collection and write to temporary file
     *
     * @return Mage_Dataflow_Model_Convert_Parser_Csv
     */
    public function unparse()
    {
        $batchExport = $this->getBatchExportModel()
            ->setBatchId($this->getBatchModel()->getId());
        $fieldList = $this->getBatchModel()->getFieldList();
        $batchExportIds = $batchExport->getIdCollection();

        if (!$batchExportIds) {
            return $this;
        }

        $io = $this->getBatchModel()->getIoAdapter();
        $io->open();

        if ($this->getVar('fieldnames')) {
            $csvData = $this->getCsvString($fieldList);
            $io->write($csvData);

			// Added By Zeon for Category Data Import
			$arrCategoryPath = NULL;
			if (preg_match("/Category Level1/i", $csvData)) {
				$arrCategoryPath = $this->getCategoryDetails();
			}
		}

        foreach ($batchExportIds as $batchExportId) {
            $csvData = array();
            $batchExport->load($batchExportId);
            $row = $batchExport->getBatchData();

            foreach ($fieldList as $field) {
                $csvData[] = isset($row[$field]) ? $row[$field] : '';
            }
            $csvData = $this->getCsvString($csvData, $arrCategoryPath);
            $io->write($csvData);
        }

        $io->close();

        return $this;
    }

    public function unparseRow($args)
    {
        $i = $args['i'];
        $row = $args['row'];

        $fDel = $this->getVar('delimiter', ',');
        $fEnc = $this->getVar('enclose', '"');
        $fEsc = $this->getVar('escape', '\\');
        $lDel = "\r\n";

        if ($fDel == '\t') {
            $fDel = "\t";
        }

        $line = array();
        foreach ($this->_fields as $f) {
            $v = isset($row[$f]) ? str_replace(array('"', '\\'), array($fEnc.'"', $fEsc.'\\'), $row[$f]) : '';
            $line[] = $fEnc.$v.$fEnc;
        }

        return join($fDel, $line);
    }

    /**
     * Retrieve csv string from array
     *
     * @param array $fields
     * @return sting
     */
    public function getCsvString($fields = array(), $arrCategoryPath=array()) {
        $delimiter  = $this->getVar('delimiter', ',');
        $enclosure  = $this->getVar('enclose', '');
        $escapeChar = $this->getVar('escape', '\\');

        if ($delimiter == '\t') {
            $delimiter = "\t";
        }

        $str = '';
		if(is_array($arrCategoryPath) && count($arrCategoryPath) > 0 ) {
			$categoryIds = explode(',',$fields[1]);

			if(is_array($categoryIds) && count($categoryIds) > 0 ) {
				$categoryPathString = NULL;
				$categoryPath = NULL;

				// BOF1 :: Added By Zeon for Category data export according to Level in seperated column with ^
				$categoryLevel1 =  NULL;
				$categoryLevel2 =  NULL;
				$categoryLevel3 =  NULL;
				$categoryLevel4 =  NULL;
				$categoryLevel5 =  NULL;
				$categoryLevel6 =  NULL;
				$categoryLevelArr1 =  array();
				$categoryLevelArr2 =  array();
				$categoryLevelArr3 =  array();
				$categoryLevelArr4 =  array();
				$categoryLevelArr5 =  array();
				$categoryLevelArr6 =  array();
				$categoryLevelStr1 =  NULL;
				$categoryLevelStr2 =  NULL;
				$categoryLevelStr3 =  NULL;
				$categoryLevelStr4 =  NULL;
				$categoryLevelStr5 =  NULL;
				$categoryLevelStr6 =  NULL;
				// EOF1 :: Added By Zeon for Category data export according to Level in seperated column with ^

				foreach($categoryIds as $catId) {
					if($catId != '2') {

						if(array_key_exists($catId, $arrCategoryPath)) {
							$categoryPath[] = $arrCategoryPath[$catId]['name_path'];
						}
					}
				}

				if(is_array($categoryPath) && count($categoryPath) > 0 ) {

					// BOF2 :: Added By Zeon for Category data export according to Level in seperated column with ^
					foreach($categoryPath as $catIndex=>$catValue){

						$catArray = @explode("^",$catValue);

						if( count($catArray) > 0){

							if(@$catArray[0] !='' && (!in_array($catArray[0],$categoryLevelArr1))){
								$categoryLevelArr1[] =  @$catArray[0];
							}
							if(@$catArray[1] !=''  && (!in_array($catArray[1],$categoryLevelArr2))){
								$categoryLevelArr2[] =  @$catArray[1];
							}
							if(@$catArray[2] !=''  && (!in_array($catArray[2],$categoryLevelArr3))){
								$categoryLevelArr3[] =  @$catArray[2];
							}
							if(@$catArray[3] !=''  && (!in_array($catArray[3],$categoryLevelArr4))){
								$categoryLevelArr4[] =  @$catArray[3];
							}
							if(@$catArray[4] !=''  && (!in_array($catArray[4],$categoryLevelArr5))){
								$categoryLevelArr5[] =  @$catArray[4];
							}
							if(@$catArray[5] !=''  && (!in_array($catArray[5],$categoryLevelArr6))){
								$categoryLevelArr6[] =  @$catArray[5];
							}
						}
					}

					if(is_array($categoryLevelArr1) && count($categoryLevelArr1) > 0 ) {
						$categoryLevelStr1 = @implode('^', $categoryLevelArr1);
					}
					if(is_array($categoryLevelArr2) && count($categoryLevelArr2) > 0 ) {
						$categoryLevelStr2 = @implode('^', $categoryLevelArr2);
					}
					if(is_array($categoryLevelArr3) && count($categoryLevelArr3) > 0 ) {
						$categoryLevelStr3 = @implode('^', $categoryLevelArr3);
					}
					if(is_array($categoryLevelArr4) && count($categoryLevelArr4) > 0 ) {
						$categoryLevelStr4 = @implode('^', $categoryLevelArr4);
					}
					if(is_array($categoryLevelArr5) && count($categoryLevelArr5) > 0 ) {
						$categoryLevelStr5 = @implode('^', $categoryLevelArr5);
					}
					if(is_array($categoryLevelArr6) && count($categoryLevelArr6) > 0 ) {
						$categoryLevelStr6 = @implode('^', $categoryLevelArr6);
					}
					// EOF2 :: Added By Zeon for Category data export according to Level in seperated column with ^
				}
			}

			// BOF3 :: Added By Zeon for Category data export according to Level in seperated column with ^
			$fields[1] = $categoryLevelStr1;
			$fields[2] = $categoryLevelStr2;
			$fields[3] = $categoryLevelStr3;
			$fields[4] = $categoryLevelStr4;
			$fields[5] = $categoryLevelStr5;
			$fields[6] = $categoryLevelStr6;
			// EOF3 :: Added By Zeon for Category data export according to Level in seperated column with ^
		}
        foreach ($fields as $value) {
            if (strpos($value, $delimiter) !== false ||
                empty($enclosure) ||
                strpos($value, $enclosure) !== false ||
                strpos($value, "\n") !== false ||
                strpos($value, "\r") !== false ||
                strpos($value, "\t") !== false ||
                strpos($value, ' ') !== false) {
                $str2 = $enclosure;
                $escaped = 0;
                $len = strlen($value);
                for ($i=0;$i<$len;$i++) {
                    if ($value[$i] == $escapeChar) {
                        $escaped = 1;
                    } else if (!$escaped && $value[$i] == $enclosure) {
                        $str2 .= $enclosure;
                    } else {
                        $escaped = 0;
                    }
                        $str2 .= $value[$i];
                }
                $str2 .= $enclosure;
                $str .= $str2.$delimiter;
            } else {
                $str .= $enclosure.$value.$enclosure.$delimiter;
            }
        }
        return substr($str, 0, -1) . "#~#\n";
    }
	/**
     * Retrieve All Category
	 *
     * @return array
     */
	public function getCategoryDetails() {
		$resource 	= Mage::getSingleton('core/resource');
		$read	= $resource->getConnection('core_read');
		$storeId = '1';

        /*$sql = "select entity_id, path from catalog_category_entity";
        $result = $read->fetchAll($sql);
        if ($result) {
			print_r($result);exit;
        }*/

		// find all categories for this store
        $sql = "SELECT entity_id, path FROM catalog_category_entity WHERE path LIKE '1/2/%'";
        $rows = $read->fetchAll($sql);
        if ($rows) {
            $categories = array();
            // create associated array
            foreach ($rows as $r) {
                $categories[$r['entity_id']] = $r;
            }
            $eav = Mage::getSingleton('eav/config');

			$nameAttrId = $eav->getAttribute('catalog_category', 'name')->getAttributeId();
            // fetch names for loaded categories
            $sql = $read->quoteInto("SELECT entity_id, value FROM catalog_category_entity_varchar WHERE attribute_id=$nameAttrId AND store_id in (0, $storeId) AND entity_id in (?) ORDER BY store_id DESC", array_keys($categories));

			$rows = $read->fetchAll($sql);
            foreach ($rows as $r) {
                // load names for specific store OR default
                if (empty($categories[$r['entity_id']]['name'])) {
                    $categories[$r['entity_id']]['name'] = $r['value'];
                }
            }
			// generate breadcrumbs for loaded categories
            foreach ($categories as $id=>&$c) {
                $path = array();
                foreach (array_slice(explode('/', $c['path']), 2) as $i) {
                    $path[] = $categories[$i]['name'];
                }
                $c['name_path'] = join('^', $path);
            }

			return $categories;
        }
	}
}
