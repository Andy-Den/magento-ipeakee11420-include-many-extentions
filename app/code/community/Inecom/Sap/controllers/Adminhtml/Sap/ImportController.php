<?php

class Inecom_Sap_Adminhtml_Sap_ImportController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('system/inecom_sap/import')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Product Import'), Mage::helper('adminhtml')->__('Product Import'));
		return $this;
	}

	public function indexAction()
    {
		$this->_initAction()
			->renderLayout();
	}

    /**
     * Manually import products
     *
     */
    public function runimportAction()
    {
        $ok = false;
        $helper = Mage::helper('inecom_sap/import');

        if ($helper->isRunning()) {
            Mage::getSingleton('core/session')->addError('It appears a process like this is already running. Please try again in a few minutes. There is a 10 minute time out in case the process failed on a previous run.');
            $this->_redirect('*/*');
        }

//        $mtime = microtime();
//        $mtime = explode(" ",$mtime);
//        $mtime = $mtime[1] + $mtime[0];
//        $starttime = $mtime;

        try {
            // Get the data
            $xml = $helper->getProducts();
            //$xml = $this->getStaticXML();

//            header("Content-Type:text/xml");
//            echo $xml;
//            exit;

            // Process xml
            $ok = $helper->process($xml);

            // Re-build affected indexes
            $process = Mage::getModel('index/process')->load(2);
            $process->reindexAll();
            $process = Mage::getModel('index/process')->load(6);
            $process->reindexAll();
            $process = Mage::getModel('index/process')->load(7);
            $process->reindexAll();

//            $mtime = microtime();
//            $mtime = explode(" ",$mtime);
//            $mtime = $mtime[1] + $mtime[0];
//            $endtime = $mtime;
//            $totaltime = ($endtime - $starttime);
//            echo "This page was created in ".$totaltime." seconds";
//            exit;

        } catch (Exception $exc) {
            Mage::getSingleton('core/session')->addError($exc->getMessage());
            $this->_redirect('*/*');
        }

        if ($ok) {
            Mage::getSingleton('core/session')->addSuccess('Product data has been synchronised.');
        } else {
            Mage::getSingleton('core/session')->addError('Something went wrong pulling products from the SAP web service. Check the log file for more information.');
        }

        $this->_redirect('*/*');
    }

     public function runexportAction()
    {
        $ok = false;
        $helper = Mage::helper('inecom_sap/export');

        if ($helper->isRunning()) {
            Mage::getSingleton('core/session')->addError('It appears a process like this is already running. Please try again in a few minutes. There is a 10 minute time out in case the process failed on a previous run.');
            $this->_redirect('*/*');
        }

        try {
            // Get the data
            $ok = $helper->exportProducts();
       


        } catch (Exception $exc) {
            Mage::getSingleton('core/session')->addError($exc->getMessage());
            $this->_redirect('*/*');
        }

        if ($ok) {
            Mage::getSingleton('core/session')->addSuccess('Product data has been exported.');
        } else {
            Mage::getSingleton('core/session')->addError('Something went wrong exporting products to the SAP web service. Check the log file for more information.');
        }

        $this->_redirect('*/*');
    }

    public function getStaticXML()
    {
        return '<?xml version=\'1.0\' encoding="UTF-8"?>
    <ListItemsTestResponse xmlns="http://tempuri.org/">
        <ListItemTestResult>
          <row>
            <ItemCode>CY0024PCZIP</ItemCode>
            <ItemName>Zip iPhone &amp; iPod Retractable 3.5 Cable</ItemName>
            <ItemGroupCode>108</ItemGroupCode>

            <ItemGroupName>Power Connectivity</ItemGroupName>
            <Colour>Black</Colour>
            <Model>Others</Model>
            <Range>Zip</Range>
            <Prices>
              <Price Country="US">6.100000</Price>

              <Price Country="GB">4.760000</Price>
              <Price Country="AU">6.800000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0088CPLAV</ItemCode>
            <ItemName>Lavish Black iPhone 4 Leather Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>

            <Colour>Black</Colour>
            <Model>iPhone 4</Model>
            <Range>Lavish</Range>
            <Prices>
              <Price Country="US">21.600000</Price>
              <Price Country="GB">16.800000</Price>

              <Price Country="AU">24.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY0092CPGLA</ItemCode>
            <ItemName>Glam Red iPhone 4 Patent Leather Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Red</Colour>

            <Model>iPhone 4</Model>
            <Range>Glam</Range>
            <Prices>
              <Price Country="US">18.900000</Price>
              <Price Country="GB">14.700000</Price>
              <Price Country="AU">21.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY0095CPGLA</ItemCode>
            <ItemName>Glam Purple iPhone 4 Patent Leather Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Purple</Colour>
            <Model>iPhone 4</Model>

            <Range>Glam</Range>
            <Prices>
              <Price Country="US">18.900000</Price>
              <Price Country="GB">14.700000</Price>
              <Price Country="AU">21.000000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0105ACCAR</ItemCode>
            <ItemName>CarGo iPad Headrest Mount</ItemName>

            <ItemGroupCode>116</ItemGroupCode>
            <ItemGroupName>Stand &amp; Holders</ItemGroupName>
            <Colour>Black</Colour>
            <Model>iPad</Model>
            <Range>Cargo</Range>

            <Prices>
              <Price Country="US">26.100000</Price>
              <Price Country="GB">20.300000</Price>
              <Price Country="AU">29.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">

                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0150LSAER</ItemCode>
            <ItemName>Aerosphere Black iPad Sleeve w Bubble Texture</ItemName>

            <ItemGroupCode>103</ItemGroupCode>
            <ItemGroupName>Bags</ItemGroupName>
            <Colour>Black</Colour>
            <Model>iPad</Model>
            <Range>Aerosphere</Range>
            <Prices>

              <Price Country="US">19.800000</Price>
              <Price Country="GB">15.400000</Price>
              <Price Country="AU">22.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0151LSAER</ItemCode>
            <ItemName>Aerosphere Grey iPad Sleeve w Bubble Texture</ItemName>
            <ItemGroupCode>103</ItemGroupCode>

            <ItemGroupName>Bags</ItemGroupName>
            <Colour>Grey</Colour>
            <Model>iPad</Model>
            <Range>Aerosphere</Range>
            <Prices>
              <Price Country="US">19.800000</Price>

              <Price Country="GB">15.400000</Price>
              <Price Country="AU">22.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0152LSAER</ItemCode>
            <ItemName>Aerosphere Brown iPad Sleeve w Bubble Texture</ItemName>
            <ItemGroupCode>103</ItemGroupCode>
            <ItemGroupName>Bags</ItemGroupName>

            <Colour>Brown</Colour>
            <Model>iPad</Model>
            <Range>Aerosphere</Range>
            <Prices>
              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>

              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY0161ACFLE</ItemCode>
            <ItemName>FlexiView iPad Adjustable Stand</ItemName>
            <ItemGroupCode>116</ItemGroupCode>
            <ItemGroupName>Stand &amp; Holders</ItemGroupName>
            <Colour>Black</Colour>

            <Model>iPad</Model>
            <Range>Flexiview</Range>
            <Prices>
              <Price Country="US">14.000000</Price>
              <Price Country="GB">10.850000</Price>
              <Price Country="AU">15.500000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY0162KBKEY</ItemCode>
            <ItemName>KeyPad iPad Wireless Bluetooth Keyboard</ItemName>
            <ItemGroupCode>114</ItemGroupCode>
            <ItemGroupName>IT</ItemGroupName>
            <Colour>Black</Colour>
            <Model>Others</Model>

            <Range>Keypad</Range>
            <Prices>
              <Price Country="US">46.800000</Price>
              <Price Country="GB">36.400000</Price>
              <Price Country="AU">52.000000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0185CNSEC</ItemCode>
            <ItemName>Second Skin Blk &amp; Clr Nano 6th G Silicon Case</ItemName>

            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Black</Colour>
            <Model>Nano 6</Model>
            <Range>Second Skin</Range>
            <Prices>

              <Price Country="US">8.600000</Price>
              <Price Country="GB">6.650000</Price>
              <Price Country="AU">9.500000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0186CNOPT</ItemCode>
            <ItemName>Optic Nano 6th G Anti-Glare x3 Screen protectors</ItemName>
            <ItemGroupCode>102</ItemGroupCode>

            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Others</Colour>
            <Model>Nano 6</Model>
            <Range>Optic</Range>
            <Prices>
              <Price Country="US">6.300000</Price>

              <Price Country="GB">4.900000</Price>
              <Price Country="AU">7.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0187CNCRY</ItemCode>
            <ItemName>Crystal Clear Nano 6th G Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>

            <Colour>Clear</Colour>
            <Model>Nano 6</Model>
            <Range>Crystal</Range>
            <Prices>
              <Price Country="US">8.600000</Price>
              <Price Country="GB">6.650000</Price>

              <Price Country="AU">9.500000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY0189SDROT</ItemCode>
            <ItemName>Revolution for iPhone &amp; iPod Rotating Speaker</ItemName>
            <ItemGroupCode>101</ItemGroupCode>
            <ItemGroupName>Speakers</ItemGroupName>
            <Colour>Black</Colour>

            <Model>Others</Model>
            <Range>Revolution</Range>
            <Prices>
              <Price Country="US">90.000000</Price>
              <Price Country="GB">70.000000</Price>
              <Price Country="AU">100.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY0190SDDNT</ItemCode>
            <ItemName>iPhonic for iPhone &amp; iPod Compact Speaker</ItemName>
            <ItemGroupCode>101</ItemGroupCode>
            <ItemGroupName>Speakers</ItemGroupName>
            <Colour>Black</Colour>

            <Model>Others</Model>
            <Range>iPhonic</Range>
            <Prices>
              <Price Country="US">81.000000</Price>
              <Price Country="GB">63.000000</Price>
              <Price Country="AU">90.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY0192S3TOT</ItemCode>
            <ItemName>Totem Grey iPhone/iPod/MP3/PC Speakers w. SD/USB slot</ItemName>
            <ItemGroupCode>101</ItemGroupCode>
            <ItemGroupName>Speakers</ItemGroupName>
            <Colour>Grey</Colour>
            <Model>Others</Model>

            <Range>Totem</Range>
            <Prices>
              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0204S3TOT</ItemCode>
            <ItemName>Totem Black iPhone/iPod/MP3/PC Speakers w. SD/USB slot</ItemName>

            <ItemGroupCode>101</ItemGroupCode>
            <ItemGroupName>Speakers</ItemGroupName>
            <Colour>Black</Colour>
            <Model>Others</Model>
            <Range>Totem</Range>
            <Prices>

              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0205S3TOT</ItemCode>
            <ItemName>Totem Red iPhone/iPod/MP3/PC Speakers w. SD/USB slot</ItemName>
            <ItemGroupCode>101</ItemGroupCode>

            <ItemGroupName>Speakers</ItemGroupName>
            <Colour>Red</Colour>
            <Model>Others</Model>
            <Range>Totem</Range>
            <Prices>
              <Price Country="US">0.000000</Price>

              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0276CAROC</ItemCode>
            <ItemName>WatchBand Black Nano 6th G Wristband</ItemName>
            <ItemGroupCode>107</ItemGroupCode>
            <ItemGroupName>Others</ItemGroupName>

            <Colour>Black</Colour>
            <Model>Nano 6</Model>
            <Range>Watchband</Range>
            <Prices>
              <Price Country="US">13.500000</Price>
              <Price Country="GB">10.500000</Price>

              <Price Country="AU">15.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY0295CILAV</ItemCode>
            <ItemName>Lavish Black iPad 2 case / stand</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Black</Colour>

            <Model>iPad 2</Model>
            <Range>Lavish</Range>
            <Prices>
              <Price Country="US">31.500000</Price>
              <Price Country="GB">24.500000</Price>
              <Price Country="AU">35.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY0296CILAV</ItemCode>
            <ItemName>Lavish Earth Sandstone iPad 2 case / stand</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Brown</Colour>
            <Model>iPad 2</Model>

            <Range>Lavish</Range>
            <Prices>
              <Price Country="US">31.500000</Price>
              <Price Country="GB">24.500000</Price>
              <Price Country="AU">35.000000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0297CILAV</ItemCode>
            <ItemName>Lavish Earth Purple iPad 2 case / stand</ItemName>

            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Purple</Colour>
            <Model>iPad 2</Model>
            <Range>Lavish</Range>
            <Prices>

              <Price Country="US">31.500000</Price>
              <Price Country="GB">24.500000</Price>
              <Price Country="AU">35.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0298CIGLA</ItemCode>
            <ItemName>Glam Red glossy iPad 2 case / stand</ItemName>
            <ItemGroupCode>102</ItemGroupCode>

            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Red</Colour>
            <Model>iPad 2</Model>
            <Range>Glam</Range>
            <Prices>
              <Price Country="US">31.500000</Price>

              <Price Country="GB">24.500000</Price>
              <Price Country="AU">35.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0299CIGLA</ItemCode>
            <ItemName>Glam Black glossy iPad 2 case / stand</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>

            <Colour>Black</Colour>
            <Model>iPad 2</Model>
            <Range>Glam</Range>
            <Prices>
              <Price Country="US">31.500000</Price>
              <Price Country="GB">24.500000</Price>

              <Price Country="AU">35.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY0300CIWIN</ItemCode>
            <ItemName>Windsor Brown iPad 2 Leather case / stand</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Brown</Colour>

            <Model>iPad 2</Model>
            <Range>Windsor</Range>
            <Prices>
              <Price Country="US">34.200000</Price>
              <Price Country="GB">26.600000</Price>
              <Price Country="AU">38.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY0301CIARM</ItemCode>
            <ItemName>Armour White iPad 2 case / stand</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>White</Colour>
            <Model>iPad 2</Model>

            <Range>Armour</Range>
            <Prices>
              <Price Country="US">36.000000</Price>
              <Price Country="GB">28.000000</Price>
              <Price Country="AU">40.000000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0302CIARM</ItemCode>
            <ItemName>Armour Black iPad 2 case / stand</ItemName>

            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Black</Colour>
            <Model>iPad 2</Model>
            <Range>Armour</Range>
            <Prices>

              <Price Country="US">36.000000</Price>
              <Price Country="GB">28.000000</Price>
              <Price Country="AU">40.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0303CISEC</ItemCode>
            <ItemName>Silicone Black iPad 2 Soft Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>

            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Black</Colour>
            <Model>iPad 2</Model>
            <Range>Second Skin</Range>
            <Prices>
              <Price Country="US">12.600000</Price>

              <Price Country="GB">9.800000</Price>
              <Price Country="AU">14.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0306CSASC</ItemCode>
            <ItemName>Optic iPad 2 Clear Screen Protector</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>

            <Colour>Others</Colour>
            <Model>iPad 2</Model>
            <Range>Optic</Range>
            <Prices>
              <Price Country="US">7.700000</Price>
              <Price Country="GB">5.950000</Price>

              <Price Country="AU">8.500000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY0307CSAGL</ItemCode>
            <ItemName>Optic iPad 2 Anti-Glare Screen Protector</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Others</Colour>

            <Model>iPad 2</Model>
            <Range>Optic</Range>
            <Prices>
              <Price Country="US">7.700000</Price>
              <Price Country="GB">5.950000</Price>
              <Price Country="AU">8.500000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY0332CBAGL</ItemCode>
            <ItemName>Optic Clear BB PlayBook Screen Protector</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Clear</Colour>
            <Model>BB Playbook</Model>

            <Range>Optic</Range>
            <Prices>
              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0333CXFOR</ItemCode>
            <ItemName>Form White Galaxy SII Slim Glossy Hard Case</ItemName>

            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>White</Colour>
            <Model>Galaxy S II</Model>
            <Range>Form</Range>
            <Prices>

              <Price Country="US">9.900000</Price>
              <Price Country="GB">7.700000</Price>
              <Price Country="AU">11.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0334CXFRO</ItemCode>
            <ItemName>Frost Red Galaxy SII Slim Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>

            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Red</Colour>
            <Model>Galaxy S II</Model>
            <Range>Frost</Range>
            <Prices>
              <Price Country="US">9.900000</Price>

              <Price Country="GB">7.700000</Price>
              <Price Country="AU">11.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0335LSAER</ItemCode>
            <ItemName>Aerosphere Black 11" MacBook Air Sleeve</ItemName>
            <ItemGroupCode>103</ItemGroupCode>
            <ItemGroupName>Bags</ItemGroupName>

            <Colour>Black</Colour>
            <Model>Others</Model>
            <Range>Aerosphere</Range>
            <Prices>
              <Price Country="US">12.600000</Price>
              <Price Country="GB">9.800000</Price>

              <Price Country="AU">14.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY0336LSAER</ItemCode>
            <ItemName>Aerosphere Grey 11" MacBook Air Sleeve</ItemName>
            <ItemGroupCode>103</ItemGroupCode>
            <ItemGroupName>Bags</ItemGroupName>
            <Colour>Grey</Colour>

            <Model>Others</Model>
            <Range>Aerosphere</Range>
            <Prices>
              <Price Country="US">12.600000</Price>
              <Price Country="GB">9.800000</Price>
              <Price Country="AU">14.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY0337LSAER</ItemCode>
            <ItemName>Aerosphere Orange 11" MacBook Air Sleeve</ItemName>
            <ItemGroupCode>103</ItemGroupCode>
            <ItemGroupName>Bags</ItemGroupName>
            <Colour>Orange</Colour>
            <Model>Others</Model>

            <Range>Aerosphere</Range>
            <Prices>
              <Price Country="US">12.600000</Price>
              <Price Country="GB">9.800000</Price>
              <Price Country="AU">14.000000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0338ACDAS</ItemCode>
            <ItemName>Car Mount for All Phones</ItemName>

            <ItemGroupCode>116</ItemGroupCode>
            <ItemGroupName>Stand &amp; Holders</ItemGroupName>
            <Colour>Black</Colour>
            <Model>iPhone 4</Model>
            <Range>Dashview</Range>

            <Prices>
              <Price Country="US">7.200000</Price>
              <Price Country="GB">5.600000</Price>
              <Price Country="AU">8.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">

                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0339POGPR</ItemCode>
            <ItemName>GroovePower iPhone /iPod Charger AU</ItemName>

            <ItemGroupCode>108</ItemGroupCode>
            <ItemGroupName>Power Connectivity</ItemGroupName>
            <Colour>White</Colour>
            <Model>Others</Model>
            <Range>Groove</Range>
            <Prices>

              <Price Country="US">14.400000</Price>
              <Price Country="GB">11.200000</Price>
              <Price Country="AU">16.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0340POGPS</ItemCode>
            <ItemName>GroovePower Smartphone Charger AU</ItemName>
            <ItemGroupCode>108</ItemGroupCode>

            <ItemGroupName>Power Connectivity</ItemGroupName>
            <Colour>Black</Colour>
            <Model>Others</Model>
            <Range>Groove</Range>
            <Prices>
              <Price Country="US">11.700000</Price>

              <Price Country="GB">9.100000</Price>
              <Price Country="AU">13.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0341POGPP</ItemCode>
            <ItemName>GroovePower + iPad/iPhone Charger AU</ItemName>
            <ItemGroupCode>108</ItemGroupCode>
            <ItemGroupName>Power Connectivity</ItemGroupName>

            <Colour>White</Colour>
            <Model>iPad 2</Model>
            <Range>Groove</Range>
            <Prices>
              <Price Country="US">18.900000</Price>
              <Price Country="GB">14.700000</Price>

              <Price Country="AU">21.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY0346PCMIC</ItemCode>
            <ItemName>Zip Mini USB to Micro USB Retractable Cable</ItemName>
            <ItemGroupCode>108</ItemGroupCode>
            <ItemGroupName>Power Connectivity</ItemGroupName>
            <Colour>Black</Colour>

            <Model>Others</Model>
            <Range>Zip</Range>
            <Prices>
              <Price Country="US">6.100000</Price>
              <Price Country="GB">4.760000</Price>
              <Price Country="AU">6.800000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY0347PCMIN</ItemCode>
            <ItemName>Zip Mini USB to Mini USB Retractable Cable</ItemName>
            <ItemGroupCode>108</ItemGroupCode>
            <ItemGroupName>Power Connectivity</ItemGroupName>
            <Colour>Black</Colour>
            <Model>Others</Model>

            <Range>Zip</Range>
            <Prices>
              <Price Country="US">6.100000</Price>
              <Price Country="GB">4.760000</Price>
              <Price Country="AU">6.800000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0348POGUK</ItemCode>
            <ItemName>GroovePower iPhone &amp; iPod Charger UK</ItemName>

            <ItemGroupCode>108</ItemGroupCode>
            <ItemGroupName>Power Connectivity</ItemGroupName>
            <Colour>White</Colour>
            <Model>Others</Model>
            <Range>Groove</Range>
            <Prices>

              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0349POGUK</ItemCode>
            <ItemName>GroovePower Smartphone Charger UK</ItemName>
            <ItemGroupCode>108</ItemGroupCode>

            <ItemGroupName>Power Connectivity</ItemGroupName>
            <Colour>Black</Colour>
            <Model>Others</Model>
            <Range>Groove</Range>
            <Prices>
              <Price Country="US">0.000000</Price>

              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0350POGUK</ItemCode>
            <ItemName>GroovePower + iPad/iPhone Charger UK</ItemName>
            <ItemGroupCode>108</ItemGroupCode>
            <ItemGroupName>Power Connectivity</ItemGroupName>

            <Colour>White</Colour>
            <Model>iPad 2</Model>
            <Range>Groove</Range>
            <Prices>
              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>

              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY0351POGEU</ItemCode>
            <ItemName>GroovePower iPhone /iPodCharger EU</ItemName>
            <ItemGroupCode>108</ItemGroupCode>
            <ItemGroupName>Power Connectivity</ItemGroupName>
            <Colour>White</Colour>

            <Model>Others</Model>
            <Range>Groove</Range>
            <Prices>
              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY0352POGEU</ItemCode>
            <ItemName>GroovePower Smartphone Charger EU</ItemName>
            <ItemGroupCode>108</ItemGroupCode>
            <ItemGroupName>Power Connectivity</ItemGroupName>
            <Colour>Black</Colour>
            <Model>Others</Model>

            <Range>Groove</Range>
            <Prices>
              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0353POGEU</ItemCode>
            <ItemName>GroovePower + iPad /iPhoneCharger EU</ItemName>

            <ItemGroupCode>108</ItemGroupCode>
            <ItemGroupName>Power Connectivity</ItemGroupName>
            <Colour>White</Colour>
            <Model>iPad 2</Model>
            <Range>Groove</Range>
            <Prices>

              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0357POBAS</ItemCode>
            <ItemName>GroovePower Base USB charger AU</ItemName>
            <ItemGroupCode>108</ItemGroupCode>

            <ItemGroupName>Power Connectivity</ItemGroupName>
            <Colour>White</Colour>
            <Model>Others</Model>
            <Range>Groove</Range>
            <Prices>
              <Price Country="US">9.500000</Price>

              <Price Country="GB">7.350000</Price>
              <Price Country="AU">10.500000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0358POBAS</ItemCode>
            <ItemName>GroovePower Base USB charger EU</ItemName>
            <ItemGroupCode>108</ItemGroupCode>
            <ItemGroupName>Power Connectivity</ItemGroupName>

            <Colour>White</Colour>
            <Model>Others</Model>
            <Range>Groove</Range>
            <Prices>
              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>

              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY0359POBAS</ItemCode>
            <ItemName>GroovePower Base USB charger UK</ItemName>
            <ItemGroupCode>108</ItemGroupCode>
            <ItemGroupName>Power Connectivity</ItemGroupName>
            <Colour>White</Colour>

            <Model>Others</Model>
            <Range>Groove</Range>
            <Prices>
              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY0360CISOU</ItemCode>
            <ItemName>Prism SmartSound Black iPad 2 TPU Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Black</Colour>
            <Model>iPad 2</Model>

            <Range>Prism</Range>
            <Prices>
              <Price Country="US">7.700000</Price>
              <Price Country="GB">5.950000</Price>
              <Price Country="AU">8.500000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0361CISOU</ItemCode>
            <ItemName>Prism SmartSound Clear iPad 2 TPU Case</ItemName>

            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Clear</Colour>
            <Model>iPad 2</Model>
            <Range>Prism</Range>
            <Prices>

              <Price Country="US">7.700000</Price>
              <Price Country="GB">5.950000</Price>
              <Price Country="AU">8.500000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0380CBFRO</ItemCode>
            <ItemName>Frost Black BB Curve Slim Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>

            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Black</Colour>
            <Model>BB 8520</Model>
            <Range>Frost</Range>
            <Prices>
              <Price Country="US">0.000000</Price>

              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0381CBFRO</ItemCode>
            <ItemName>Frost Purple BB Curve Slim Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>

            <Colour>Purple</Colour>
            <Model>BB 8520</Model>
            <Range>Frost</Range>
            <Prices>
              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>

              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY0382CBCRY</ItemCode>
            <ItemName>Crystal Clear BB Curve Slim Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Clear</Colour>

            <Model>BB 8520</Model>
            <Range>Crystal</Range>
            <Prices>
              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY0383CBCHR</ItemCode>
            <ItemName>Chromatic Sil/Blk BB Curve Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Silver</Colour>
            <Model>BB 8520</Model>

            <Range>Chromatic</Range>
            <Prices>
              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0384CBLAV</ItemCode>
            <ItemName>Lavish Black BB Curve Leather Case</ItemName>

            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Black</Colour>
            <Model>BB 8520</Model>
            <Range>Lavish</Range>
            <Prices>

              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0387CSAGL</ItemCode>
            <ItemName>Optic iPad 2 Anti-Glare Screen Protector-2 pack</ItemName>
            <ItemGroupCode>102</ItemGroupCode>

            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Others</Colour>
            <Model>iPad 2</Model>
            <Range>Optic</Range>
            <Prices>
              <Price Country="US">0.000000</Price>

              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0391CHFRO</ItemCode>
            <ItemName>Frost Black HTC Desire S Slim Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>

            <Colour>Black</Colour>
            <Model>Desire</Model>
            <Range>Frost</Range>
            <Prices>
              <Price Country="US">9.900000</Price>
              <Price Country="GB">7.700000</Price>

              <Price Country="AU">11.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY0392CSAGL</ItemCode>
            <ItemName>Optic Clear HTC Desire S Screen Protector</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Clear</Colour>

            <Model>Desire</Model>
            <Range>Crystal</Range>
            <Prices>
              <Price Country="US">9.900000</Price>
              <Price Country="GB">7.700000</Price>
              <Price Country="AU">11.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY0393CHCRY</ItemCode>
            <ItemName>Crystal HTC Desire S Slim Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Clear</Colour>
            <Model>Desire</Model>

            <Range>Crystal</Range>
            <Prices>
              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0394CHLAV</ItemCode>
            <ItemName>Lavish Black HTC Desire S Leather Case</ItemName>

            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Black</Colour>
            <Model>Desire</Model>
            <Range>Lavish</Range>
            <Prices>

              <Price Country="US">22.500000</Price>
              <Price Country="GB">17.500000</Price>
              <Price Country="AU">25.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0395CHFRO</ItemCode>
            <ItemName>Frost Purple HTC Desire S Slim Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>

            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Purple</Colour>
            <Model>Desire</Model>
            <Range>Frost</Range>
            <Prices>
              <Price Country="US">9.900000</Price>

              <Price Country="GB">7.700000</Price>
              <Price Country="AU">11.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0396CHRAD</ItemCode>
            <ItemName>Radiant Clear HTC Desire S TPU Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>

            <Colour>Clear</Colour>
            <Model>Desire</Model>
            <Range>Radiant</Range>
            <Prices>
              <Price Country="US">9.900000</Price>
              <Price Country="GB">7.700000</Price>

              <Price Country="AU">11.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY0397CHRAD</ItemCode>
            <ItemName>Radiant Grey HTC Desire S TPU Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Grey</Colour>

            <Model>Desire</Model>
            <Range>Radiant</Range>
            <Prices>
              <Price Country="US">9.900000</Price>
              <Price Country="GB">7.700000</Price>
              <Price Country="AU">11.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY0398CHRAD</ItemCode>
            <ItemName>Radiant Red HTC Desire S TPU Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Red</Colour>
            <Model>Desire</Model>

            <Range>Radiant</Range>
            <Prices>
              <Price Country="US">9.900000</Price>
              <Price Country="GB">7.700000</Price>
              <Price Country="AU">11.000000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0399CDRAD</ItemCode>
            <ItemName>Radiant Blue HTC Desire S TPU Case</ItemName>

            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Blue</Colour>
            <Model>Desire</Model>
            <Range>Radiant</Range>
            <Prices>

              <Price Country="US">9.900000</Price>
              <Price Country="GB">7.700000</Price>
              <Price Country="AU">11.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0403CBFRO</ItemCode>
            <ItemName>Frost Blue BB Curve Slim Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>

            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Blue</Colour>
            <Model>BB 8520</Model>
            <Range>Frost</Range>
            <Prices>
              <Price Country="US">0.000000</Price>

              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0404CBFRO</ItemCode>
            <ItemName>Frost Red BB Curve Slim Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>

            <Colour>Red</Colour>
            <Model>BB 8520</Model>
            <Range>Frost</Range>
            <Prices>
              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>

              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY0424CPAES</ItemCode>
            <ItemName>Aerosphere Black iPhone 4 TPU case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Black</Colour>

            <Model>iPhone 4</Model>
            <Range>Aerosphere</Range>
            <Prices>
              <Price Country="US">8.600000</Price>
              <Price Country="GB">6.650000</Price>
              <Price Country="AU">9.500000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY0425CPAES</ItemCode>
            <ItemName>Aerosphere Crystal iPhone 4 TPU case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Clear</Colour>
            <Model>iPhone 4</Model>

            <Range>Aerosphere</Range>
            <Prices>
              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0426CPWOR</ItemCode>
            <ItemName>Workmate Pro Blk &amp; Gry iPhone 4 Silicone</ItemName>

            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Black</Colour>
            <Model>iPhone 4</Model>
            <Range>Workmate</Range>
            <Prices>

              <Price Country="US">15.300000</Price>
              <Price Country="GB">11.900000</Price>
              <Price Country="AU">17.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0427CPWO</ItemCode>
            <ItemName>Workmate Pro Gry &amp; Org iPhone 4 Silicone</ItemName>
            <ItemGroupCode>102</ItemGroupCode>

            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Orange</Colour>
            <Model>iPhone 4</Model>
            <Range>Workmate</Range>
            <Prices>
              <Price Country="US">15.300000</Price>

              <Price Country="GB">11.900000</Price>
              <Price Country="AU">17.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0430CPBOS</ItemCode>
            <ItemName>Boston Black iPhone 4 Leather case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>

            <Colour>Black</Colour>
            <Model>iPhone 4</Model>
            <Range>Boston</Range>
            <Prices>
              <Price Country="US">22.500000</Price>
              <Price Country="GB">17.500000</Price>

              <Price Country="AU">25.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY0432CPARC</ItemCode>
            <ItemName>Arcade Black iPhone 4 PC  Pattern case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Black</Colour>

            <Model>iPhone 4</Model>
            <Range>Arcade</Range>
            <Prices>
              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY0434CPNOM</ItemCode>
            <ItemName>Nomad Black iPhone 4 PC Grip Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Black</Colour>
            <Model>iPhone 4</Model>

            <Range>Nomad</Range>
            <Prices>
              <Price Country="US">12.600000</Price>
              <Price Country="GB">9.800000</Price>
              <Price Country="AU">14.000000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0435CPTAC</ItemCode>
            <ItemName>Tactile Black iPhone 4 PU/Material case</ItemName>

            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Black</Colour>
            <Model>iPhone 4</Model>
            <Range>Tactile</Range>
            <Prices>

              <Price Country="US">15.300000</Price>
              <Price Country="GB">11.900000</Price>
              <Price Country="AU">17.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0436CPIMP</ItemCode>
            <ItemName>Imperial Black iPhone 4 PU/Material case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>

            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Black</Colour>
            <Model>iPhone 4</Model>
            <Range>Imperial</Range>
            <Prices>
              <Price Country="US">0.000000</Price>

              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0437CPIMP</ItemCode>
            <ItemName>Imperial White iPhone 4 PU/Material case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>

            <Colour>White</Colour>
            <Model>iPhone 4</Model>
            <Range>Imperial</Range>
            <Prices>
              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>

              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY0438CPURB</ItemCode>
            <ItemName>Urban Shield Black iPhone 4 PC/Metal case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Black</Colour>

            <Model>iPhone 4</Model>
            <Range>Urban</Range>
            <Prices>
              <Price Country="US">15.300000</Price>
              <Price Country="GB">11.900000</Price>
              <Price Country="AU">17.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY0439CPURB</ItemCode>
            <ItemName>Urban Shield Silver iPhone 4 PC/Metal case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Silver</Colour>
            <Model>iPhone 4</Model>

            <Range>Urban</Range>
            <Prices>
              <Price Country="US">15.300000</Price>
              <Price Country="GB">11.900000</Price>
              <Price Country="AU">17.000000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0440CPURB</ItemCode>
            <ItemName>Urban Shield Bronze iPhone 4 PC/Metal case</ItemName>

            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Others</Colour>
            <Model>iPhone 4</Model>
            <Range>Urban</Range>
            <Prices>

              <Price Country="US">15.300000</Price>
              <Price Country="GB">11.900000</Price>
              <Price Country="AU">17.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0441CPURB</ItemCode>
            <ItemName>Urban Shield Blue iPhone 4 PC/Metal case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>

            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Blue</Colour>
            <Model>iPhone 4</Model>
            <Range>Urban</Range>
            <Prices>
              <Price Country="US">15.300000</Price>

              <Price Country="GB">11.900000</Price>
              <Price Country="AU">17.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0442CPAEE</ItemCode>
            <ItemName>AeroGrip Edge Blk iPhone 4 TPU case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>

            <Colour>Black</Colour>
            <Model>iPhone 4</Model>
            <Range>Aerogrip</Range>
            <Prices>
              <Price Country="US">8.600000</Price>
              <Price Country="GB">6.650000</Price>

              <Price Country="AU">9.500000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY0443CPAEE</ItemCode>
            <ItemName>AeroGrip Edge Gry iPhone 4 TPU case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Grey</Colour>

            <Model>iPhone 4</Model>
            <Range>Aerogrip</Range>
            <Prices>
              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY0444APAEE</ItemCode>
            <ItemName>AeroGrip Edge Red iPhone 4 TPU case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Red</Colour>
            <Model>iPhone 4</Model>

            <Range>Aerogrip</Range>
            <Prices>
              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0445CPTRA</ItemCode>
            <ItemName>Transition - Black</ItemName>

            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Black</Colour>
            <Model>iPhone 4</Model>
            <Range>Transition</Range>
            <Prices>

              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0446CPTRA</ItemCode>
            <ItemName>Transition - White</ItemName>
            <ItemGroupCode>102</ItemGroupCode>

            <ItemGroupName>Cases</ItemGroupName>
            <Colour>White</Colour>
            <Model>iPhone 4</Model>
            <Range>Transition</Range>
            <Prices>
              <Price Country="US">0.000000</Price>

              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0447CPAEG</ItemCode>
            <ItemName>AeroGrip Black iPhone 4 CDMA/GSM Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>

            <Colour>Black</Colour>
            <Model>iPhone 4</Model>
            <Range>Aerogrip</Range>
            <Prices>
              <Price Country="US">8.900000</Price>
              <Price Country="GB">6.930000</Price>

              <Price Country="AU">9.900000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY0448CPAEG</ItemCode>
            <ItemName>AeroGrip Purple iPhone 4 CDMA/GSM Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Purple</Colour>

            <Model>iPhone 4</Model>
            <Range>Aerogrip</Range>
            <Prices>
              <Price Country="US">9.900000</Price>
              <Price Country="GB">7.700000</Price>
              <Price Country="AU">11.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY0449CPAEG</ItemCode>
            <ItemName>AeroGrip Red iPhone 4 CDMA/GSM Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Red</Colour>
            <Model>iPhone 4</Model>

            <Range>Aerogrip</Range>
            <Prices>
              <Price Country="US">9.900000</Price>
              <Price Country="GB">7.700000</Price>
              <Price Country="AU">11.000000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0450CPAEG</ItemCode>
            <ItemName>AeroGrip Yellow iPhone 4 CDMA/GSM Case</ItemName>

            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Yellow</Colour>
            <Model>iPhone 4</Model>
            <Range>Aerogrip</Range>
            <Prices>

              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0451CPAEG</ItemCode>
            <ItemName>AeroGrip White iPhone 4 CDMA/GSM Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>

            <ItemGroupName>Cases</ItemGroupName>
            <Colour>White</Colour>
            <Model>iPhone 4</Model>
            <Range>Aerogrip</Range>
            <Prices>
              <Price Country="US">9.900000</Price>

              <Price Country="GB">7.700000</Price>
              <Price Country="AU">11.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0454CXFOR</ItemCode>
            <ItemName>Form Black Galaxy SII Slim Glossy Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>

            <Colour>Black</Colour>
            <Model>Galaxy S II</Model>
            <Range>Form</Range>
            <Prices>
              <Price Country="US">9.900000</Price>
              <Price Country="GB">7.700000</Price>

              <Price Country="AU">11.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY0455CXFOR</ItemCode>
            <ItemName>Form Purple Galaxy SII Slim Glossy Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Purple</Colour>

            <Model>Galaxy S II</Model>
            <Range>Form</Range>
            <Prices>
              <Price Country="US">9.900000</Price>
              <Price Country="GB">7.700000</Price>
              <Price Country="AU">11.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY0460CXFRO</ItemCode>
            <ItemName>Frost Black Galaxy S II Slim Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Black</Colour>
            <Model>Galaxy S II</Model>

            <Range>Frost</Range>
            <Prices>
              <Price Country="US">9.900000</Price>
              <Price Country="GB">7.700000</Price>
              <Price Country="AU">11.000000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0461CXFRO</ItemCode>
            <ItemName>Frost Purple Galaxy S II Slim Hard Case</ItemName>

            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Purple</Colour>
            <Model>Galaxy S II</Model>
            <Range>Frost</Range>
            <Prices>

              <Price Country="US">9.900000</Price>
              <Price Country="GB">7.700000</Price>
              <Price Country="AU">11.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0462CXCRY</ItemCode>
            <ItemName>Crystal Clear Galaxy S II Slim Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>

            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Clear</Colour>
            <Model>Galaxy S II</Model>
            <Range>Crystal</Range>
            <Prices>
              <Price Country="US">9.900000</Price>

              <Price Country="GB">7.700000</Price>
              <Price Country="AU">11.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0463CXLAV</ItemCode>
            <ItemName>Lavish Black Galaxy S II Leather Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>

            <Colour>Black</Colour>
            <Model>Galaxy S II</Model>
            <Range>Lavish</Range>
            <Prices>
              <Price Country="US">18.000000</Price>
              <Price Country="GB">14.000000</Price>

              <Price Country="AU">20.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY0464CXOPT</ItemCode>
            <ItemName>Optic Clear Galaxy S II Screen Protector</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Clear</Colour>

            <Model>Galaxy S II</Model>
            <Range>Optic</Range>
            <Prices>
              <Price Country="US">6.300000</Price>
              <Price Country="GB">4.900000</Price>
              <Price Country="AU">7.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY0465CBLAV</ItemCode>
            <ItemName>Lavish Earth Sandstone BB PlayBook case / stand</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Brown</Colour>
            <Model>BB Playbook</Model>

            <Range>Lavish</Range>
            <Prices>
              <Price Country="US">29.300000</Price>
              <Price Country="GB">22.750000</Price>
              <Price Country="AU">32.500000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0466CBLAV</ItemCode>
            <ItemName>Lavish Earth Purple BB PlayBook case / stand</ItemName>

            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Purple</Colour>
            <Model>BB Playbook</Model>
            <Range>Lavish</Range>
            <Prices>

              <Price Country="US">29.300000</Price>
              <Price Country="GB">22.750000</Price>
              <Price Country="AU">32.500000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0467CBLAV</ItemCode>
            <ItemName>Lavish Earth Black BB PlayBook case / stand</ItemName>
            <ItemGroupCode>102</ItemGroupCode>

            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Black</Colour>
            <Model>BB Playbook</Model>
            <Range>Lavish</Range>
            <Prices>
              <Price Country="US">29.300000</Price>

              <Price Country="GB">22.750000</Price>
              <Price Country="AU">32.500000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0495CBGLA</ItemCode>
            <ItemName>Ultra Glitter Black BB PlayBook case / stand</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>

            <Colour>Black</Colour>
            <Model>BB Playbook</Model>
            <Range>Glam</Range>
            <Prices>
              <Price Country="US">35.100000</Price>
              <Price Country="GB">27.300000</Price>

              <Price Country="AU">39.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY0537S3BLA</ItemCode>
            <ItemName>Blast Yellow Portable Speaker SD\USB Slot</ItemName>
            <ItemGroupCode>101</ItemGroupCode>
            <ItemGroupName>Speakers</ItemGroupName>
            <Colour>Yellow</Colour>

            <Model>Others</Model>
            <Range>Blast</Range>
            <Prices>
              <Price Country="US">31.500000</Price>
              <Price Country="GB">24.500000</Price>
              <Price Country="AU">35.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY0538S3BLA</ItemCode>
            <ItemName>Blast Pink Portable Speaker SD\USB Slot</ItemName>
            <ItemGroupCode>101</ItemGroupCode>
            <ItemGroupName>Speakers</ItemGroupName>
            <Colour>Pink</Colour>
            <Model>Others</Model>

            <Range>Blast</Range>
            <Prices>
              <Price Country="US">31.500000</Price>
              <Price Country="GB">24.500000</Price>
              <Price Country="AU">35.000000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0539S3BLA</ItemCode>
            <ItemName>Blast White Portable Speaker SD\USB Slot</ItemName>

            <ItemGroupCode>101</ItemGroupCode>
            <ItemGroupName>Speakers</ItemGroupName>
            <Colour>White</Colour>
            <Model>Others</Model>
            <Range>Blast</Range>
            <Prices>

              <Price Country="US">31.500000</Price>
              <Price Country="GB">24.500000</Price>
              <Price Country="AU">35.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0540S3BLA</ItemCode>
            <ItemName>Blast Blue Portable Speaker SD\USB Slot</ItemName>
            <ItemGroupCode>101</ItemGroupCode>

            <ItemGroupName>Speakers</ItemGroupName>
            <Colour>Blue</Colour>
            <Model>Others</Model>
            <Range>Blast</Range>
            <Prices>
              <Price Country="US">31.500000</Price>

              <Price Country="GB">24.500000</Price>
              <Price Country="AU">35.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0541S3BLA</ItemCode>
            <ItemName>Blast Green Portable Speaker SD\USB Slot</ItemName>
            <ItemGroupCode>101</ItemGroupCode>
            <ItemGroupName>Speakers</ItemGroupName>

            <Colour>Green</Colour>
            <Model>Others</Model>
            <Range>Blast</Range>
            <Prices>
              <Price Country="US">31.500000</Price>
              <Price Country="GB">24.500000</Price>

              <Price Country="AU">35.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY0546CHFRO</ItemCode>
            <ItemName>Frost Black HTC Incredible S Slim Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Black</Colour>

            <Model>Incredible S</Model>
            <Range>Frost</Range>
            <Prices>
              <Price Country="US">9.900000</Price>
              <Price Country="GB">7.700000</Price>
              <Price Country="AU">11.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY0547CHFRO</ItemCode>
            <ItemName>Frost Purple HTC Incredible S Slim Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Purple</Colour>
            <Model>Incredible S</Model>

            <Range>Frost</Range>
            <Prices>
              <Price Country="US">9.900000</Price>
              <Price Country="GB">7.700000</Price>
              <Price Country="AU">11.000000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0548CHCRY</ItemCode>
            <ItemName>Crystal Clear HTC Incredible S Slim Hard Case</ItemName>

            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Clear</Colour>
            <Model>Incredible S</Model>
            <Range>Crystal</Range>
            <Prices>

              <Price Country="US">9.900000</Price>
              <Price Country="GB">7.700000</Price>
              <Price Country="AU">11.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0552CMFRO</ItemCode>
            <ItemName>Frost Black Motorola Atrix Slim Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>

            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Black</Colour>
            <Model>Atrix</Model>
            <Range>Frost</Range>
            <Prices>
              <Price Country="US">9.900000</Price>

              <Price Country="GB">7.700000</Price>
              <Price Country="AU">11.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0553CMFRO</ItemCode>
            <ItemName>Frost Purple Motorola Atrix Slim Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>

            <Colour>Purple</Colour>
            <Model>Atrix</Model>
            <Range>Frost</Range>
            <Prices>
              <Price Country="US">9.900000</Price>
              <Price Country="GB">7.700000</Price>

              <Price Country="AU">11.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY0554CMCRY</ItemCode>
            <ItemName>Crystal Clear Motorola Atrix Slim Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Clear</Colour>

            <Model>Atrix</Model>
            <Range>Crystal</Range>
            <Prices>
              <Price Country="US">9.900000</Price>
              <Price Country="GB">7.700000</Price>
              <Price Country="AU">11.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY0556CHFRO</ItemCode>
            <ItemName>Frost Black HTC Sensation Skim Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Black</Colour>
            <Model>Sensation S</Model>

            <Range>Frost</Range>
            <Prices>
              <Price Country="US">9.900000</Price>
              <Price Country="GB">7.700000</Price>
              <Price Country="AU">11.000000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0557CHFRO</ItemCode>
            <ItemName>Frost Purple HTC Sensation Skim Hard Case</ItemName>

            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Purple</Colour>
            <Model>Sensation S</Model>
            <Range>Frost</Range>
            <Prices>

              <Price Country="US">9.900000</Price>
              <Price Country="GB">7.700000</Price>
              <Price Country="AU">11.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0558CHCRY</ItemCode>
            <ItemName>Crystal Clear HTC Sensation Slim Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>

            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Clear</Colour>
            <Model>Sensation S</Model>
            <Range>Crystal</Range>
            <Prices>
              <Price Country="US">9.900000</Price>

              <Price Country="GB">7.700000</Price>
              <Price Country="AU">11.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0564HESPA</ItemCode>
            <ItemName>Spacebuds Silver iPods &amp; MP3 Earphones</ItemName>
            <ItemGroupCode>105</ItemGroupCode>

            <ItemGroupName>Headphones</ItemGroupName>
            <Colour>Silver</Colour>
            <Model>Others</Model>
            <Range>Spacebuds</Range>
            <Prices>
              <Price Country="US">6.800000</Price>

              <Price Country="GB">5.250000</Price>
              <Price Country="AU">7.500000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0565HEFUS</ItemCode>
            <ItemName>Fusion II Black iPod &amp; MP3 Earphones w Mic</ItemName>
            <ItemGroupCode>105</ItemGroupCode>

            <ItemGroupName>Headphones</ItemGroupName>
            <Colour>Black</Colour>
            <Model>Others</Model>
            <Range>Fusion II</Range>
            <Prices>
              <Price Country="US">15.300000</Price>

              <Price Country="GB">11.900000</Price>
              <Price Country="AU">17.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0566HEFUS</ItemCode>
            <ItemName>Fusion II White iPod &amp; MP3 Earphones w Mic</ItemName>
            <ItemGroupCode>105</ItemGroupCode>

            <ItemGroupName>Headphones</ItemGroupName>
            <Colour>White</Colour>
            <Model>Others</Model>
            <Range>Fusion II</Range>
            <Prices>
              <Price Country="US">15.300000</Price>

              <Price Country="GB">11.900000</Price>
              <Price Country="AU">17.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0567HEFUS</ItemCode>
            <ItemName>Fusion II Orange iPod &amp; MP3 Earphones w Mic</ItemName>
            <ItemGroupCode>105</ItemGroupCode>

            <ItemGroupName>Headphones</ItemGroupName>
            <Colour>Orange</Colour>
            <Model>Others</Model>
            <Range>Fusion II</Range>
            <Prices>
              <Price Country="US">15.300000</Price>

              <Price Country="GB">11.900000</Price>
              <Price Country="AU">17.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0568HEFUS</ItemCode>
            <ItemName>Fusion II Purple iPod &amp; MP3 Earphones w Mic</ItemName>
            <ItemGroupCode>105</ItemGroupCode>

            <ItemGroupName>Headphones</ItemGroupName>
            <Colour>Purple</Colour>
            <Model>Others</Model>
            <Range>Fusion II</Range>
            <Prices>
              <Price Country="US">15.300000</Price>

              <Price Country="GB">11.900000</Price>
              <Price Country="AU">17.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0569HEATO</ItemCode>
            <ItemName>Atomic II Black iPod &amp; MP3 Earphones w Mic</ItemName>
            <ItemGroupCode>105</ItemGroupCode>

            <ItemGroupName>Headphones</ItemGroupName>
            <Colour>Black</Colour>
            <Model>Others</Model>
            <Range>Atomic II</Range>
            <Prices>
              <Price Country="US">15.300000</Price>

              <Price Country="GB">11.900000</Price>
              <Price Country="AU">17.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0570HEATO</ItemCode>
            <ItemName>Atomic II Pink iPod &amp; MP3 Earphones w Mic</ItemName>
            <ItemGroupCode>105</ItemGroupCode>

            <ItemGroupName>Headphones</ItemGroupName>
            <Colour>Pink</Colour>
            <Model>Others</Model>
            <Range>Atomic II</Range>
            <Prices>
              <Price Country="US">15.300000</Price>

              <Price Country="GB">11.900000</Price>
              <Price Country="AU">17.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0571HEATO</ItemCode>
            <ItemName>Atomic II Orange iPod &amp; MP3 Earphones w Mic</ItemName>
            <ItemGroupCode>105</ItemGroupCode>

            <ItemGroupName>Headphones</ItemGroupName>
            <Colour>Orange</Colour>
            <Model>Others</Model>
            <Range>Atomic II</Range>
            <Prices>
              <Price Country="US">15.300000</Price>

              <Price Country="GB">11.900000</Price>
              <Price Country="AU">17.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0572HERAZ</ItemCode>
            <ItemName>Razor II Yellow/Black iPod &amp; MP3 Earphones</ItemName>
            <ItemGroupCode>105</ItemGroupCode>

            <ItemGroupName>Headphones</ItemGroupName>
            <Colour>Yellow</Colour>
            <Model>Others</Model>
            <Range>Razor II</Range>
            <Prices>
              <Price Country="US">13.500000</Price>

              <Price Country="GB">10.500000</Price>
              <Price Country="AU">15.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0573HERAZ</ItemCode>
            <ItemName>Razor II Red/Black iPod &amp; MP3 Earphones</ItemName>
            <ItemGroupCode>105</ItemGroupCode>

            <ItemGroupName>Headphones</ItemGroupName>
            <Colour>Red</Colour>
            <Model>Others</Model>
            <Range>Razor II</Range>
            <Prices>
              <Price Country="US">13.500000</Price>

              <Price Country="GB">10.500000</Price>
              <Price Country="AU">15.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0574HESON</ItemCode>
            <ItemName>Sonic Black iPod &amp; MP3 Headphones</ItemName>
            <ItemGroupCode>105</ItemGroupCode>

            <ItemGroupName>Headphones</ItemGroupName>
            <Colour>Black</Colour>
            <Model>Others</Model>
            <Range>Sonic</Range>
            <Prices>
              <Price Country="US">22.500000</Price>

              <Price Country="GB">17.500000</Price>
              <Price Country="AU">25.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0579SDAUD</ItemCode>
            <ItemName>AudioAxis iPad speaker dock</ItemName>
            <ItemGroupCode>101</ItemGroupCode>
            <ItemGroupName>Speakers</ItemGroupName>

            <Colour>Black</Colour>
            <Model>Others</Model>
            <Range>Audioaxis</Range>
            <Prices>
              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>

              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY0580SDSOU</ItemCode>
            <ItemName>AudioAxis iPad speaker dock</ItemName>
            <ItemGroupCode>101</ItemGroupCode>
            <ItemGroupName>Speakers</ItemGroupName>
            <Colour>Black</Colour>

            <Model>Others</Model>
            <Range>Soundscape</Range>
            <Prices>
              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY-3-GT2</ItemCode>
            <ItemName>GrooveTrip II iPhone &amp; iPod FM Transmitter</ItemName>
            <ItemGroupCode>104</ItemGroupCode>
            <ItemGroupName>FM Transmitters</ItemGroupName>
            <Colour>Black</Colour>

            <Model>Others</Model>
            <Range>Groove</Range>
            <Prices>
              <Price Country="US">23.400000</Price>
              <Price Country="GB">18.200000</Price>
              <Price Country="AU">26.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY-3-PBM</ItemCode>
            <ItemName>Groove Platinum Blk iPods &amp; MP3 Earphone w Mic</ItemName>
            <ItemGroupCode>105</ItemGroupCode>
            <ItemGroupName>Headphones</ItemGroupName>
            <Colour>Black</Colour>

            <Model>Others</Model>
            <Range>Groove</Range>
            <Prices>
              <Price Country="US">18.000000</Price>
              <Price Country="GB">14.000000</Price>
              <Price Country="AU">20.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY-3-PWM</ItemCode>
            <ItemName>Groove Platinum Whi iPods &amp; MP3 Earphone w Mic</ItemName>
            <ItemGroupCode>105</ItemGroupCode>
            <ItemGroupName>Headphones</ItemGroupName>
            <Colour>White</Colour>

            <Model>Others</Model>
            <Range>Groove</Range>
            <Prices>
              <Price Country="US">18.000000</Price>
              <Price Country="GB">14.000000</Price>
              <Price Country="AU">20.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY-A-GPAU</ItemCode>
            <ItemName>GroovePower iPhone &amp; iPod AU Charger</ItemName>
            <ItemGroupCode>108</ItemGroupCode>
            <ItemGroupName>Power Connectivity</ItemGroupName>
            <Colour>Black</Colour>

            <Model>Others</Model>
            <Range>Groove</Range>
            <Prices>
              <Price Country="US">14.400000</Price>
              <Price Country="GB">11.200000</Price>
              <Price Country="AU">16.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY-A-PM</ItemCode>
            <ItemName>PowerMini iPhone &amp; iPod USB Car Charger</ItemName>
            <ItemGroupCode>108</ItemGroupCode>
            <ItemGroupName>Power Connectivity</ItemGroupName>
            <Colour>Black</Colour>

            <Model>Others</Model>
            <Range>PowerMini</Range>
            <Prices>
              <Price Country="US">9.500000</Price>
              <Price Country="GB">7.350000</Price>
              <Price Country="AU">10.500000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY-A-S2</ItemCode>
            <ItemName>Safari II iPhone &amp; iPod FM Transmitter</ItemName>
            <ItemGroupCode>104</ItemGroupCode>
            <ItemGroupName>FM Transmitters</ItemGroupName>
            <Colour>Black</Colour>

            <Model>Others</Model>
            <Range>Safari II</Range>
            <Prices>
              <Price Country="US">57.600000</Price>
              <Price Country="GB">44.800000</Price>
              <Price Country="AU">64.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY-A-TR</ItemCode>
            <ItemName>Groove Transmit iPod FM Transmitter</ItemName>
            <ItemGroupCode>104</ItemGroupCode>
            <ItemGroupName>FM Transmitters</ItemGroupName>
            <Colour>Black</Colour>
            <Model>Others</Model>

            <Range>Groove</Range>
            <Prices>
              <Price Country="US">66.600000</Price>
              <Price Country="GB">51.800000</Price>
              <Price Country="AU">74.000000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY-B-A13B</ItemCode>
            <ItemName>"Aerosphere Black 13"" MacBook/Pro Sleeve w Bubble Texture"</ItemName>

            <ItemGroupCode>103</ItemGroupCode>
            <ItemGroupName>Bags</ItemGroupName>
            <Colour>Black</Colour>
            <Model>Others</Model>
            <Range>Aerosphere</Range>
            <Prices>

              <Price Country="US">0.000000</Price>
              <Price Country="GB">0.000000</Price>
              <Price Country="AU">0.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY-B-B10B</ItemCode>
            <ItemName>Aerosphere Black 10"" PC Sleeve w Bubble Texture</ItemName>
            <ItemGroupCode>103</ItemGroupCode>

            <ItemGroupName>Bags</ItemGroupName>
            <Colour>Black</Colour>
            <Model>Others</Model>
            <Range>Aerosphere</Range>
            <Prices>
              <Price Country="US">12.600000</Price>

              <Price Country="GB">9.800000</Price>
              <Price Country="AU">14.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY-B-B10G</ItemCode>
            <ItemName>"Aerosphere Grey 10"" PC Sleeve w Bubble Texture"</ItemName>
            <ItemGroupCode>103</ItemGroupCode>
            <ItemGroupName>Bags</ItemGroupName>

            <Colour>Grey</Colour>
            <Model>Others</Model>
            <Range>Aerosphere</Range>
            <Prices>
              <Price Country="US">12.600000</Price>
              <Price Country="GB">9.800000</Price>

              <Price Country="AU">14.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY-B-B10O</ItemCode>
            <ItemName>"Aerosphere Orange 10"" PC Sleeve w Bubble Texture"</ItemName>
            <ItemGroupCode>103</ItemGroupCode>
            <ItemGroupName>Bags</ItemGroupName>
            <Colour>Orange</Colour>

            <Model>Others</Model>
            <Range>Aerosphere</Range>
            <Prices>
              <Price Country="US">12.600000</Price>
              <Price Country="GB">9.800000</Price>
              <Price Country="AU">14.000000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY-B-B13B</ItemCode>
            <ItemName>"Aerosphere Black 13"" PC Sleeve w Bubble Texture"</ItemName>
            <ItemGroupCode>103</ItemGroupCode>
            <ItemGroupName>Bags</ItemGroupName>
            <Colour>Black</Colour>
            <Model>Others</Model>

            <Range>Aerosphere</Range>
            <Prices>
              <Price Country="US">16.200000</Price>
              <Price Country="GB">12.600000</Price>
              <Price Country="AU">18.000000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY-B-B13G</ItemCode>
            <ItemName>"Aerosphere Grey 13"" PC Sleeve w Bubble Texture"</ItemName>

            <ItemGroupCode>103</ItemGroupCode>
            <ItemGroupName>Bags</ItemGroupName>
            <Colour>Grey</Colour>
            <Model>Others</Model>
            <Range>Aerosphere</Range>
            <Prices>

              <Price Country="US">16.200000</Price>
              <Price Country="GB">12.600000</Price>
              <Price Country="AU">18.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY-B-B13O</ItemCode>
            <ItemName>"Aerosphere Orange 13"" PC Sleeve w Bubble Texture"</ItemName>
            <ItemGroupCode>103</ItemGroupCode>

            <ItemGroupName>Bags</ItemGroupName>
            <Colour>Orange</Colour>
            <Model>Others</Model>
            <Range>Aerosphere</Range>
            <Prices>
              <Price Country="US">16.200000</Price>

              <Price Country="GB">12.600000</Price>
              <Price Country="AU">18.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
          <row>
            <ItemCode>CY-B-B15B</ItemCode>
            <ItemName>"Aerosphere Black 15"" PC Sleeve w Bubble Texture"</ItemName>
            <ItemGroupCode>103</ItemGroupCode>
            <ItemGroupName>Bags</ItemGroupName>

            <Colour>Black</Colour>
            <Model>Others</Model>
            <Range>Aerosphere</Range>
            <Prices>
              <Price Country="US">20.300000</Price>
              <Price Country="GB">15.750000</Price>

              <Price Country="AU">22.500000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>

          <row>
            <ItemCode>CY-B-B15G</ItemCode>
            <ItemName>"Aerosphere Grey 15"" PC Sleeve w Bubble Texture"</ItemName>
            <ItemGroupCode>103</ItemGroupCode>
            <ItemGroupName>Bags</ItemGroupName>
            <Colour>Grey</Colour>

            <Model>Others</Model>
            <Range>Aerosphere</Range>
            <Prices>
              <Price Country="US">20.300000</Price>
              <Price Country="GB">15.750000</Price>
              <Price Country="AU">22.500000</Price>

            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>

            <ItemCode>CY-B-B15O</ItemCode>
            <ItemName>"Aerosphere Orange15"" PC Sleeve w Bubble Texture"</ItemName>
            <ItemGroupCode>103</ItemGroupCode>
            <ItemGroupName>Bags</ItemGroupName>
            <Colour>Orange</Colour>
            <Model>Others</Model>

            <Range>Aerosphere</Range>
            <Prices>
              <Price Country="US">20.300000</Price>
              <Price Country="GB">15.750000</Price>
              <Price Country="AU">22.500000</Price>
            </Prices>
            <Warehouses>

              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY-C-CC</ItemCode>
            <ItemName>Crystal Clear iPod Classic Hard Case</ItemName>

            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Clear</Colour>
            <Model>Classic</Model>
            <Range>Crystal</Range>
            <Prices>

              <Price Country="US">9.900000</Price>
              <Price Country="GB">7.700000</Price>
              <Price Country="AU">11.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>

              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY-C-JB</ItemCode>
            <ItemName>Jellybean Black iPod Classic 2 Part Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>

            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Black</Colour>
            <Model>Classic</Model>
            <Range>Jellybean</Range>
            <Prices>
              <Price Country="US">8.100000</Price>

              <Price Country="GB">6.300000</Price>
              <Price Country="AU">9.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>

            </Warehouses>
          </row>
        </ListItemTestResult>
      </ListItemsTestResponse>
        ';
    }
}