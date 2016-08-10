<?php

class Exceedz_AddDispatchDate_Model_Observer {

	public function addDispatchDate($observer) {

		Mage::log("_______________________start___________________________________", null , 'debugging.log' , true);
		try{
			$items = $observer->getQuote()->getAllItems();
			foreach($items as $item){

				$product = Mage::getModel('catalog/product')->load($item->getProductId());
				Mage::log($product->getId(), null , 'debugging.log' , true);

				$num_of_b_days = null;
				$date = Mage::helper('amstockstatus')->getDispatchDate($product, $num_of_b_days);
				Mage::log('amstockstatus: '.$num_of_b_days. ', '. $date, null, 'debugging.log', true);

				$date = empty($date) ? $this->getDispatchDate($product) : $date;
				
				$date = Mage::app()->getLocale()->date($date)->toString('Y-m-d', 'php');

				Mage::log($date, null , 'debugging.log' , true);
				
				$item->setDispatchDate($date);
				
				$item->setDispatchNote(Mage::helper('amstockstatus')->getDispatchNote($num_of_b_days));

			//$item->save();
			}
		}catch(Exception $e){
			Mage::log($e->getMessage(), null , 'debugging.log' , true);
		}
		Mage::log("______________________________end____________________________", null , 'debugging.log' , true);

	}


	public function getDispatchDate($product){

		if($product->getPreorderCalender()){
			Mage::log('PreorderCalender date', null , 'debugging.log' , true);
			return $product->getPreorderCalender();
		}else{
			$now = Mage::app()->getLocale()->date();
			Mage::log(Mage::helper('core')->formatDate($now, 'full', true), null , 'debugging.log' , true);
			$now = $this->getNextBusinessDay($now);
			Mage::log(Mage::helper('core')->formatDate($now, 'full', true), null , 'debugging.log' , true);
			return $now;
		}
	}

	public function getNextBusinessDay($date){
		$nextDay = $date->addDay(1);
		$nextDayOfWeek = $nextDay->get(Zend_Date::WEEKDAY);
		if($nextDayOfWeek == 'Saturday' || $nextDayOfWeek == 'Sunday'){
			return $this->getNextBusinessDay($nextDay);
		}else{
			return $nextDay;
		}
	}

}