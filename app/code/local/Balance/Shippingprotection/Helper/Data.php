<?php

/**
 * Balance_Shippingprotection_Helper_Data
 *
 * @author Balance Internet
 */
class Balance_Shippingprotection_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getTargetPlatform()
    {
        if (!array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
            return "desktop";
        }
        $ua = $_SERVER['HTTP_USER_AGENT'];
        // Check if session has already started, otherwise E_NOTICE is thrown

        // Check if user agent is a smart TV - http://goo.gl/FocDk
        if ((preg_match(
            '/GoogleTV|SmartTV|Internet.TV|NetCast|NETTV|AppleTV|boxee|Kylo|Roku|DLNADOC|CE\-HTML/i',
            $ua
        ))
        ) {
            $device = "tv";
        } // Check if user agent is a TV Based Gaming Console
        else {
            if ((preg_match('/Xbox|PLAYSTATION.3|Wii/i', $ua))) {
                $device = "tv";
            } // Check if user agent is a Tablet
            else {
                if ((preg_match('/iP(a|ro)d/i', $ua))
                    || (preg_match('/tablet/i', $ua)) && (!preg_match('/RX-34/i', $ua))
                    || (preg_match('/FOLIO/i', $ua))
                ) {
                    $device = "tablet";
                } // Check if user agent is an Android Tablet
                else {
                    if ((preg_match('/Linux/i', $ua)) && (preg_match('/Android/i', $ua))
                        && (!preg_match(
                            '/Fennec|mobi|HTC.Magic|HTCX06HT|Nexus.One|SC-02B|fone.945/i',
                            $ua
                        ))
                    ) {
                        $device = "tablet";
                    } // Check if user agent is a Kindle or Kindle Fire
                    else {
                        if ((preg_match('/Kindle/i', $ua))
                            || (preg_match('/Mac.OS/i', $ua)) && (preg_match('/Silk/i', $ua))
                        ) {
                            $device = "tablet";
                        } // Check if user agent is a pre Android 3.0 Tablet
                        else {
                            if ((preg_match(
                                    '/GT-P10|SC-01C|SHW-M180S|SGH-T849|SCH-I800|SHW-M180L|SPH-P100|SGH-I987|zt180|HTC(.Flyer|\_Flyer)|Sprint.ATP51|ViewPad7|pandigital(sprnova|nova)|Ideos.S7|Dell.Streak.7|Advent.Vega|A101IT|A70BHT|MID7015|Next2|nook/i',
                                    $ua
                                ))
                                || (preg_match('/MB511/i', $ua)) && (preg_match('/RUTEM/i', $ua))
                            ) {
                                $device = "tablet";
                            } // Check if user agent is unique Mobile User Agent
                            else {
                                if ((preg_match(
                                    '/BOLT|Fennec|Iris|Maemo|Minimo|Mobi|mowser|NetFront|Novarra|Prism|RX-34|Skyfire|Tear|XV6875|XV6975|Google.Wireless.Transcoder/i',
                                    $ua
                                ))
                                ) {
                                    $device = "mobile";
                                } // Check if user agent is an odd Opera User Agent - http://goo.gl/nK90K
                                else {
                                    if ((preg_match('/Opera/i', $ua)) && (preg_match('/Windows.NT.5/i', $ua))
                                        && (preg_match(
                                            '/HTC|Xda|Mini|Vario|SAMSUNG\-GT\-i8000|SAMSUNG\-SGH\-i9/i',
                                            $ua
                                        ))
                                    ) {
                                        $device = "mobile";
                                    } // Check if user agent is Windows Desktop
                                    else {
                                        if ((preg_match('/Windows.(NT|XP|ME|9)/', $ua))
                                            && (!preg_match(
                                                '/Phone/i',
                                                $ua
                                            ))
                                            || (preg_match('/Win(9|.9|NT)/i', $ua))
                                        ) {
                                            $device = "desktop";
                                        } // Check if agent is Mac Desktop
                                        else {
                                            if ((preg_match('/Macintosh|PowerPC/i', $ua))
                                                && (!preg_match(
                                                    '/Silk/i',
                                                    $ua
                                                ))
                                            ) {
                                                $device = "desktop";
                                            } // Check if user agent is a Linux Desktop
                                            else {
                                                if ((preg_match('/Linux/i', $ua)) && (preg_match('/X11/i', $ua))) {
                                                    $device = "desktop";
                                                } // Check if user agent is a Solaris, SunOS, BSD Desktop
                                                else {
                                                    if ((preg_match('/Solaris|SunOS|BSD/i', $ua))) {
                                                        $device = "desktop";
                                                    } // Check if user agent is a Desktop BOT/Crawler/Spider
                                                    else {
                                                        if ((preg_match(
                                                                '/Bot|Crawler|Spider|Yahoo|ia_archiver|Covario-IDS|findlinks|DataparkSearch|larbin|Mediapartners-Google|NG-Search|Snappy|Teoma|Jeeves|TinEye/i',
                                                                $ua
                                                            ))
                                                            && (!preg_match('/Mobile/i', $ua))
                                                        ) {
                                                            $device = "desktop";
                                                        } // Otherwise assume it is a Mobile Device
                                                        else {
                                                            $device = "desktop";
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $device;

    }

}
