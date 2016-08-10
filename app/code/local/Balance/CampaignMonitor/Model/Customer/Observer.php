<?php

/**
 * Balance Campaign Monitor Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com and you will be sent a copy immediately.
 *
 * @category   Balance
 * @package    Balance_CampaignMonitor
 * @author     Peter Spiller
 * @author     Chris Norton
 * @copyright  Copyright (c) 2008 Balance Pty. Ltd. (http://www.balance.com.au)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SubscriberCustomField
{
    function SubscriberCustomField($k, $v)
    {
        $this->Key = $k;
        $this->Value = $v;
    }
}

class Balance_CampaignMonitor_Model_Customer_Observer
{
    public function check_subscription_status($observer)
    {
        $event = $observer->getEvent();
        $customer = $event->getCustomer();

        $apiKey = trim(Mage::getStoreConfig('newsletter/campaignmonitor/api_key'));
        $listID = trim(Mage::getStoreConfig('newsletter/campaignmonitor/list_id'));

        $name = $customer->getFirstname() . " " . $customer->getLastname();
        $newEmail = $customer->getEmail();
        $subscribed = $customer->getIsSubscribed();

        $oldEmail = Mage::getModel('customer/customer')->load($customer->getId())->getEmail();
        // if subscribed is NULL (i.e. because the form didn't set it one way
        // or the other), get the existing value from the database
        if ($subscribed === null) {
            $subscribed = Mage::getModel('newsletter/subscriber')->loadByCustomer($customer)->isSubscribed();
        }


        if ($apiKey and $listID) {
            $customFields = Balance_CampaignMonitor_Model_Customer_Observer::generateCustomFields($customer);

            try {
                $client = new SoapClient("http://api.createsend.com/api/api.asmx?wsdl", array("trace" => true));
            } catch (Exception $e) {
                Mage::log("Balance_CampaignMonitor: Error connecting to CampaignMonitor server: " . $e->getMessage());

                return;
            }

            if ($subscribed) {
                /* If the customer:

                   1) Already exists (i.e. has an old email address)
                   2) Has changed their email address

                   unsubscribe their old address. */
                if ($oldEmail and $newEmail != $oldEmail) {
                    Mage::log("Balance_CampaignMonitor: Unsubscribing old email address: $oldEmail");
                    try {
                        $client->Unsubscribe(
                            array(
                                "ApiKey" => $apiKey,
                                "ListID" => $listID,
                                "Email"  => $oldEmail
                            )
                        );
                    } catch (Exception $e) {
                        Mage::log("Balance_CampaignMonitor: Error in SOAP call: " . $e->getMessage());

                        return;
                    }
                }

                // Using 'add and resubscribe' rather than just 'add', otherwise
                // somebody who unsubscribes and resubscribes won't be put back
                // on the active list
                Mage::log("Balance_CampaignMonitor: Subscribing new email address: $newEmail");
                try {
                    $client->AddAndResubscribeWithCustomFields(
                        array(
                            "ApiKey"       => $apiKey,
                            "ListID"       => $listID,
                            "Email"        => $newEmail,
                            "Name"         => $name,
                            "CustomFields" => $customFields
                        )
                    );
                } catch (Exception $e) {
                    Mage::log("Balance_CampaignMonitor: Error in SOAP call: " . $e->getMessage());

                    return;
                }
            } else {
                Mage::log("Balance_CampaignMonitor: Unsubscribing: $oldEmail");

                try {
                    $client->Unsubscribe(
                        array(
                            "ApiKey" => $apiKey,
                            "ListID" => $listID,
                            "Email"  => $oldEmail
                        )
                    );
                } catch (Exception $e) {
                    Mage::log("Balance_CampaignMonitor: Error in SOAP call: " . $e->getMessage());

                    return;
                }
            }
        }
    }

    public function customer_deleted($observer)
    {
        $event = $observer->getEvent();
        $customer = $event->getCustomer();

        $apiKey = trim(Mage::getStoreConfig('newsletter/campaignmonitor/api_key'));
        $listID = trim(Mage::getStoreConfig('newsletter/campaignmonitor/list_id'));

        $email = $customer->getEmail();

        if ($apiKey and $listID) {
            Mage::log("Balance_CampaignMonitor: Customer deleted, unsubscribing: $email");
            try {
                $client = new SoapClient("http://api.createsend.com/api/api.asmx?wsdl");
                $client->Unsubscribe(
                    array(
                        "ApiKey" => $apiKey,
                        "ListID" => $listID,
                        "Email"  => $email
                    )
                );
            } catch (Exception $e) {
                Mage::log("Balance_CampaignMonitor: Error in SOAP call: " . $e->getMessage());

                return;
            }
        }
    }

    // get array of linked attributes from the config settings and
    // populate it
    public static function generateCustomFields($customer)
    {
        $linkedAttributes = @unserialize(
            Mage::getStoreConfig(
                'newsletter/campaignmonitor/m_to_cm_attributes',
                Mage::app()->getStore()->getStoreId()
            )
        );
        $customFields = array();
        if (!empty($linkedAttributes)) {
            $customerData = $customer->getData();
            foreach ($linkedAttributes as $la) {
                $magentoAtt = $la['magento'];
                $cmAtt = $la['campaignmonitor'];

                // try and translate IDs to names where possible
                if ($magentoAtt == 'group_id') {
                    $d = Mage::getModel('customer/group')->load($customer->getGroupId())->getData();
                    if (array_key_exists('customer_group_code', $d)) {
                        $customFields[] = array("Key" => $cmAtt, "Value" => $d['customer_group_code']);
                    }
                } else {
                    if ($magentoAtt == 'website_id') {
                        $d = Mage::getModel('core/website')->load($customer->getWebsiteId())->getData();
                        if (array_key_exists('name', $d)) {
                            $customFields[] = array("Key" => $cmAtt, "Value" => $d['name']);
                        }
                    } else {
                        if ($magentoAtt == 'store_id') {
                            $d = Mage::getModel('core/store')->load($customer->getStoreId())->getData();
                            if (array_key_exists('name', $d)) {
                                $customFields[] = array("Key" => $cmAtt, "Value" => $d['name']);
                            }
                        } else {
                            if (strncmp('FONTIS', $magentoAtt, 6) == 0) {
                                $d = false;
                                // 15 == strlen('FONTIS-billing-')
                                if (strncmp('FONTIS-billing', $magentoAtt, 14) == 0) {
                                    $d = $customer->getDefaultBillingAddress();
                                    if ($d) {
                                        $d = $d->getData();
                                        $addressAtt = substr($magentoAtt, 15, strlen($magentoAtt));
                                    }
                                } // 16 == strlen('FONTIS-shipping-')
                                else {
                                    $d = $customer->getDefaultShippingAddress();
                                    if ($d) {
                                        $d = $d->getData();
                                        $addressAtt = substr($magentoAtt, 16, strlen($magentoAtt));
                                    }
                                }

                                if ($d and $addressAtt == 'country_id') {
                                    if (array_key_exists('country_id', $d)) {
                                        $country = Mage::getModel('directory/country')->load($d['country_id']);
                                        $customFields[] = array("Key", $d => $cmAtt, "Value" => $country->getName());
                                    }
                                } else {
                                    if ($d) {
                                        if (array_key_exists($addressAtt, $d)) {
                                            $customFields[] = array("Key" => $cmAtt, "Value" => $d[$addressAtt]);
                                        }
                                    }
                                }
                            } else {
                                if (array_key_exists($magentoAtt, $customerData)) {
                                    $customFields[] = array("Key" => $cmAtt, "Value" => $customerData[$magentoAtt]);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $customFields;
    }

    /**
     * Add subscriber to Campaign Monitor list
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function addSubscriberToList(Varien_Event_Observer $observer)
    {
        $session = Mage::getSingleton('core/session');
        $email = $observer->getEvent()->getEmail();
        $name = $observer->getEvent()->getCustomerName();

        $apiKey = trim(Mage::getStoreConfig('newsletter/campaignmonitor/api_key'));
        $listID = trim(Mage::getStoreConfig('newsletter/campaignmonitor/list_id'));

        if ($apiKey && $listID) {
            try {
                $client = new SoapClient("http://api.createsend.com/api/api.asmx?wsdl", array("trace" => true));
            } catch (Exception $e) {
                Mage::log("Balance_CampaignMonitor: Error connecting to CampaignMonitor server: " . $e->getMessage());
                $session->addException($e, $this->__('There was a problem with the subscription'));
            }

            $customerHelper = Mage::helper('customer');
            if ($customerHelper->isLoggedIn()) {
                $customer = $customerHelper->getCustomer();
                $name = $customer->getFirstname() . " " . $customer->getLastname();
                $customFields = Balance_CampaignMonitor_Model_Customer_Observer::generateCustomFields($customer);
                try {
                    $client->AddAndResubscribeWithCustomFields(
                        array(
                            "ApiKey"       => $apiKey,
                            "ListID"       => $listID,
                            "Email"        => $email,
                            "Name"         => $name,
                            "CustomFields" => $customFields
                        )
                    );
                } catch (Exception $e) {
                    Mage::log("Balance_CampaignMonitor: Error in CampaignMonitor SOAP call: " . $e->getMessage());
                    $session->addException($e, $this->__('There was a problem with the subscription'));
                }
            } else {
                try {
                    $client->AddAndResubscribe(
                        array(
                            "ApiKey" => $apiKey,
                            "ListID" => $listID,
                            "Email"  => $email,
                            "Name"   => $name
                        )
                    );
                } catch (Exception $e) {
                    Mage::log("Balance_CampaignMonitor: Error in CampaignMonitor SOAP call: " . $e->getMessage());
                    $session->addException($e, $this->__('There was a problem with the subscription'));
                }
            }
        } else {
            Mage::log(
                "Balance_CampaignMonitor: Error: Campaign Monitor API key and/or list ID not set in Magento Newsletter options."
            );
        }
    }
}
