<?php

/**
 * Override GoogleAnalytics
 *
 * @category  Milandirect
 * @package   Milandirect_GoogleAnalytics
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2016 Balance
 */
class Milandirect_GoogleAnalytics_Block_Ga extends Mage_GoogleAnalytics_Block_Ga
{
    /**
     * Render GA tracking scripts
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::helper('googleanalytics')->isGoogleAnalyticsAvailable()) {
            return '';
        }
        $accountId = Mage::getStoreConfig(Mage_GoogleAnalytics_Helper_Data::XML_PATH_ACCOUNT);
        return '
            <!-- BEGIN GOOGLE ANALYTICS CODE -->
            <script type="text/javascript">
            //<![CDATA[
                (function() {
                    var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;
                    ga.src = (\'https:\' == document.location.protocol ? \'https://\' : \'http://\') + \'.stats.g.doubleclick.net/dc.js\';
                    (document.getElementsByTagName(\'head\')[0] || document.getElementsByTagName(\'body\')[0]).appendChild(ga);
                })();

                var _gaq = _gaq || [];
            ' . $this->_getPageTrackingCode($accountId) . '
            ' . $this->_getOrdersTrackingCode() . '
            //]]>
            </script>
            <!-- END GOOGLE ANALYTICS CODE -->';
    }
}
