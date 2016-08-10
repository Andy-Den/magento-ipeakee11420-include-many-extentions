<?php
/**
 * Loads GeoIP binary data files
 */
class Exceedz_GeoIp_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
     * Load GeoIP binary data file
     *
     * @return string
     */
	public function loadGeoIp()
	{
		// Load geoip.inc
		include_once(Mage::getBaseDir().'/var/geoip/geoip.inc');

		// For Latest Database path
		//http://geolite.maxmind.com/download/geoip/database/GeoLiteCountry/GeoIP.dat.gz

		// Open Geo IP binary data file
		$geoIp = geoip_open(Mage::getBaseDir().'/var/geoip/GeoIP.dat',GEOIP_STANDARD);

		return $geoIp;
	}

	/**
     * Get IP Address
     *
     * @return string
     */
	public function getIpAddress()
	{
		return $_SERVER['REMOTE_ADDR'];
		//return "115.113.227.130";//India
		//return "86.141.47.163";//UK
		//return "203.8.183.255";//AU
        //return "58.28.255.255";//New Zealand
	}

	/**
     * Get Country Code
     *
     * @return string
     */
	public function getCountryName()
	{
		// Set default country code
		//$countryCode = 'AU';

		// load GeoIP binary data file
		$geoIp = $this->loadGeoIp();

		$ipAddress =$this->getIpAddress();

		// get country code from ip address
		$country = geoip_country_name_by_addr($geoIp, $ipAddress);

		// close the geo database
		geoip_close($geoIp);

		if($country != '') {
			$countryCode = $country;
		}

		return $countryCode;
	}
}