<?php
/**
 * @author  Balance Internet
 */
class Balance_Addressbypostcode_Model_Config_Import extends Mage_Adminhtml_Model_System_Config_Backend_File
{
    /**
     * Process additional data before save config
     *
     * @return Balance_Addressbypostcode_Model_Config_Import
     */
    protected function _beforeSave()
    {
        if (!isset($_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value'])) {
            return $this;
        }
        $tmpPath = $_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value'];
        if ($tmpPath && file_exists($tmpPath)) {
            if (!filesize($tmpPath)) {
                Mage::throwException(Mage::helper('addressbypostcode')->__('Address file is empty.'));
            }
            // importing address from Csv file.
            $row = 1;
            if (($handle = fopen($tmpPath, "r")) !== false) {
                $tbName = Mage::getModel('core/resource_setup')->getTable("addressbypostcode/address");
                $write = Mage::getSingleton("core/resource")->getConnection("core_write");
                $sql1 = "DELETE from {$tbName}";
                $sql2 = "INSERT INTO {$tbName} (pcode,locality,state,comment,category) VALUES ";
                $hasDataFlag = false;
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    if ($row == 1) {
                        $row++;
                        continue;
                    }
                    if (!$data[0] || !$data[1] || !$data[2]) {
                        continue;
                    }
                    $data[0] = str_pad($data[0], 4, '0', STR_PAD_LEFT);
                    foreach ($data as $key => $value) {
                        $data[$key] = addslashes($value);
                    }
                    $sql2 .= "('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]'),";
                    $hasDataFlag = true;
                }
                $sql2 = substr($sql2, 0, -1);
                $sql2 .= ";";
                $write->query($sql1);
                if ($hasDataFlag) {
                    $write->query($sql2);
                }
                fclose($handle);
            }
        }

        return $this;
    }
}