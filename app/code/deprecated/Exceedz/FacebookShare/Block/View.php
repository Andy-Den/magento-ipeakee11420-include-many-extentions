<?php
/**
 * FacebookShare View Block
 *
 * @category   Exceedz
 * @package    Exceedz_FacebookShare
 */
class Exceedz_FacebookShare_Block_View extends Mage_Core_Block_Template
{
    public function getShareButton()
    {

    }

    /**
     * Get the facebook share count for specified URL
     * @param string $pageUrl - URL of page that is to be shared.
     * @return int $fbCount
     */
    public function getCount($pageUrl)
    {
    	$fbCount = 0;
		$facebookContent = file_get_contents('http://api.facebook.com/restserver.php?method=links.getStats&urls=' . $pageUrl);
		$fbBegin = '<share_count>';
		$fbEnd = '</share_count>';
		$fbPage = $facebookContent;
		$fbParts = explode($fbBegin, $fbPage);
		$fbPage = $fbParts[1];
		$fbParts = explode($fbEnd,$fbPage);
		$fbCount = $fbParts[0];
		if($fbCount == '') {
			$fbCount = '0';
		}

		return $fbCount;
    }
}