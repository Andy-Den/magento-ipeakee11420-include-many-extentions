<?php

class Nick_Trackingimport_Model_Observer
{

  public function Fileimport()
  {

  	if(Mage::getStoreConfig('trackingimport/cron_settings/active') == 1){

		$fileName = $this->Getfiles();
		if (!$fileName) return;

		$localdir = Mage::getStoreConfig('trackingimport/cron_settings/localdir');

		$csvObject  = new Varien_File_Csv();
		$csvObject->setDelimiter(Mage::getStoreConfig('trackingimport/general/delimiter'));
		$csvObject->setEnclosure(Mage::getStoreConfig('trackingimport/general/enclosure'));
        $csvData = $csvObject->getData($fileName);
      //  var_dump($csvData);
	//		exit;
        $csvFields  = array(
            0   => Mage::getStoreConfig('trackingimport/csvheaders/orderid'),
            1   => Mage::getStoreConfig('trackingimport/csvheaders/shipmentid'),
			2   => Mage::getStoreConfig('trackingimport/csvheaders/carrierid'),
        );


    	foreach ($csvData as $k => $v) {

				$orderId = $v[0];
				$trackingNum = $v[1];
				$carrierTitle = $v[2];

			if (Mage::getStoreConfig('trackingimport/general/skip') == 1){
						if ($k == 0) {
							continue;
						}
					}

			try {

				Mage::getModel('Trackingimport/import')->BeginImport($orderId, $trackingNum, $carrierTitle);

			} catch (Mage_Core_Exception $e) {
			    Mage::log("$e->getMessage()");
			   return;
			}
		}

  		 if (Mage::getStoreConfig('trackingimport/cron_settings/cron_archive') == 1) {
  			$new_path = Mage::getBaseDir().'/var/import/'.$localdir.'/imported/archived'.date("d-m-y-h-i").".csv";

			copy($fileName, $new_path);
		}
  		unlink($fileName);

  		return;
  	}
  }

  public function Getfiles()
  {
   		$localdir = Mage::getStoreConfig('trackingimport/cron_settings/localdir');

   		$dir= Mage::getBaseDir().'/var/import/'.$localdir;
   		$max=2;
   		$files=Array();
   		$f=opendir($dir);

   		while (($file=readdir($f))!==false) {
	   		if(is_file("$dir/$file")) $files[]=Array($file,filemtime("$dir/$file"));
		}

	   	$fileCount=0;
	   	closedir($f);
	   	$m=min($max,count($files));
		for ($i=0;$i<$m;$i++) {
			$fileName = $files[$i][0];
			$fileCount++;

			$target_path = Mage::getBaseDir().'/var/import/'.$localdir.'/'.$fileName;
			if (file_exists($target_path))return $target_path;
		}
  		return false;
	}
}
