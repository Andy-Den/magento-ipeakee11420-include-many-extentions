<?php
/**
 * Html page block
 *
 * @category   Exceedz
 * @package   Exceedz_Page
 */
class Exceedz_Page_Block_Html_Head extends Mage_Page_Block_Html_Head
{
    /**
     * Return OS and browser name with version
     *
     * @return array
     */
    public function getOsAndBrowserDetail()
    {
       $browser = $this->_getBrowser();
       $os = $this->_getOS();
       return array(
            'browser'      => $browser['name'].$browser['version'],
            'os'           => $os['name']
        );
    }
    
     /**
     * Return browser name
     *
     * @return array
     */
    private function _getBrowser()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $browserName = 'Unknown';
        $versionRequireFor = array();
        $version = '';
        
        // Next get the name of the useragent yes seperately and for good reason
        if(preg_match('/MSIE/i',$userAgent) && !preg_match('/Opera/i',$userAgent)) {
            $browserName = 'Internet Explorer';
            $ub = "MSIE"; 
            $versionRequireFor[] = $ub;
        } elseif(preg_match('/Firefox/i',$userAgent)) {
            $browserName = 'Mozilla Firefox';
            $ub = "Firefox"; 
        } elseif(preg_match('/Chrome/i',$userAgent)) {
            $browserName = 'Google Chrome';
            $ub = "Chrome"; 
        } elseif(preg_match('/Safari/i',$userAgent)) {
            $browserName = 'Apple Safari';
            $ub = "Safari"; 
        } elseif(preg_match('/Opera/i',$userAgent)) {
            $browserName = 'Opera';
            $ub = "Opera"; 
        } elseif(preg_match('/Netscape/i',$userAgent)) {
            $browserName = 'Netscape';
            $ub = "Netscape"; 
        }
       
        if(in_array($ub, $versionRequireFor)) {
            // finally get the correct version number
            $known = array('Version', $ub, 'other');
            $pattern = '#(?<browser>' . join('|', $known) .
            ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
            if (!preg_match_all($pattern, $userAgent, $matches)) {
                // we have no matching number just continue
            }
            
            // see how many we have
            $i = count($matches['browser']);
            if ($i != 1) {
                //we will have two since we are not using 'other' argument yet
                //see if version is before or after the name
                if (strripos($userAgent,"Version") < strripos($userAgent,$ub)){
                    $version = $matches['version'][0];
                }
                else {
                    $version = $matches['version'][1];
                }
            }
            else {
                $version = $matches['version'][0];
            }
           
            // check if we have a number
            if ($version==null || $version=="") {$version="?";}
        }
        return array(
            'name'      => $browserName,
            'version'   => $version
        );
    }
    
     /**
     * Return OS name with version
     *
     * @return array
     */
    private function _getOS()
    {
        $OS = 'Unknown';
        $OSList = array(
			// Match user agent string with operating systems
			'Windows 3.11' => 'Win16',
			'Windows 95' => '(Windows 95)',
			'Windows 98' => '(Windows 98)',
			'Windows 2000' => '(Windows NT 5.0)',
			'Windows XP' => '(Windows NT 5.1)',
			'Windows Server 2003' => '(Windows NT 5.2)',
			'Windows Vista' => '(Windows NT 6.0)',
			'Windows 7' => '(Windows NT 7.0)',
			'Windows 7' => '(Windows NT 6.1)',
			'Windows NT 4.0' => '(Windows NT 4.0)',
			'Windows ME' => 'Windows ME',
			'Open BSD' => 'OpenBSD',
			'Sun OS' => 'SunOS',
			'Linux' => '(Linux)',
			'Mac' => '(Mac)',
			'QNX' => 'QNX',
			'BeOS' => 'BeOS',
			'OS/2' => 'OS/2',
			'Search Bot'=>'(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp)|(MSNBot)|(Ask Jeeves/Teoma)|(ia_archiver)'
		);
		// Loop through the array of user agents and matching operating systems
		foreach($OSList as $currentOS=>$match){
            // Find a match
			if (preg_match($match, $_SERVER['HTTP_USER_AGENT'])){
                // We found the correct match
                $OS = $currentOS;
				break;
			}
		}
        
        return array(
            'name'      => $OS
        );
    }
}