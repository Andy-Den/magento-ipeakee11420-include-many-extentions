<?php

class Inecom_Sap_IndexController extends Mage_Core_Controller_Front_Action
{
    public function importAction()
    {
        $import = Mage::getModel('importexport/import');

//        $validationResult = $import->validateSource($import->setData(array('import_file' => Mage::getBaseDir().'/tmp/fortunato-test.csv'))->uploadSource());

//        var_dump($validationResult);
        //var_dump(Mage::getBaseDir().'/tmp/fortunato-test.csv');


        echo "Respect my authoritah";
        exit;
        //new SimpleXMLElement()

    }

    public function updateorderAction() {


        $helper = Mage::helper('inecom_sap/update');

        //$xml = $helper->getUpdates();
		try {
		  if (!($xml = file_get_contents('php://input'))) {
			throw new Exception('Could not read POST data.');
		  }
		} catch (Exception $e) {
		  print('Did not successfully process HTTP request: '.$e->getMessage());
		  exit;
		}

        $searchArray = array(
            '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"><SOAP-ENV:Body>',
            '</SOAP-ENV:Body></SOAP-ENV:Envelope>');
        $normalizedXml = str_replace($searchArray, array('', ''), $xml);
        $res = $helper->processUpdates($normalizedXml);
        echo "This is how it went";
        print_r($res);
        exit();
    }

}