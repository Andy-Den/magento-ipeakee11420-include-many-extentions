<?php

class Innobyte_ProductQuestions_Helper_Data extends Mage_Core_Helper_Abstract
{

    const ADMIN_EMAIL_TEMPLATE = 'Innobyte_ProductQuestions/emails/admintemplate';
    const CUSTOMER_EMAIL_TEMPLATE = 'Innobyte_ProductQuestions/emails/customertemplate';
    const EMAIL_SENDER = 'Innobyte_ProductQuestions/emails/sender';
    const EMAIL_RECIPIENT = 'Innobyte_ProductQuestions/emails/email';

    /**
     * Returned format: April 23rd, 2013
     * @param string $date
     * @return string 'April 23rd, 2013'
     */
    public function dateFormat($date)
    {   
        $formatedDate = date('F jS, Y',strtotime($date));   
        
        return $formatedDate;
    }

    public function getSender($storeId = null)
    {
        $senderCode = Mage::getStoreConfig(self::EMAIL_SENDER, $storeId);
        $sender = array(
            'name' => Mage::getStoreConfig('trans_email/ident_' . $senderCode . '/name',
                                           $storeId),
            'mail' => Mage::getStoreConfig('trans_email/ident_' . $senderCode . '/email',
                                           $storeId),
        );

        return $sender;
    }

    public function sendEmailToAdmin($question, $vendorEmail = null)
    {
        // send email to admin about new question
        $store = Mage::app()->getStore();
        $storeId = $store->getId();
        $mailTemplate = Mage::getModel('core/email_template');
        try {
            $sender = $this->getSender();
            if (method_exists($mailTemplate->getMail(), 'setReplyTo')) {
                $mailTemplate->getMail()
                    ->setReplyTo($sender['mail'], $sender['name']);
            } else {
                $mailTemplate->getMail()
                    ->addHeader('Reply-To', $sender['mail']);
            }
        }
        catch (Exception $ex) {
            
        }

        $recipient = Mage::getStoreConfig(self::EMAIL_RECIPIENT);

        $translate = Mage::getSingleton('core/translate');
        $mailTemplate
            ->setDesignConfig(array(
                'area' => 'adminhtml',
                'store' => Mage_Core_Model_App::ADMIN_STORE_ID,
            ))
            ->sendTransactional(
                Mage::getStoreConfig(self::ADMIN_EMAIL_TEMPLATE),
                                     Mage::getStoreConfig(self::EMAIL_SENDER),
                                                          $recipient, null,
                                                          array('data' => $question),
                                                          $storeId
        );
        $translate->setTranslateInline(true);

        if (!$mailTemplate->getSentSuccess()) {
            Mage::log('An error occured while sending ' .
                'Product Questions email from ' .
                Mage::getStoreConfig(self::EMAIL_SENDER) . ' to admin ' .
                $recipient .
                'using template ' .
                Mage::getStoreConfig(self::ADMIN_EMAIL_TEMPLATE) .
                ', asked by ' . $question->getCustomerEmail() .
                ', the question ' .
                'is ' . $question->getContent()
            );
        }
    }

    public function sendEmailToCustomer($question)
    {
        $storeId = $question->getStoreId();
        $store = Mage::app()->getStore($storeId);
        $product = Mage::getModel('catalog/product')
            ->load($question->getProductId());

        $mailTemplate = Mage::getModel('core/email_template');
        try {
            $sender = $this->getSender($storeId);
            if (method_exists($mailTemplate->getMail(), 'setReplyTo')) {
                $mailTemplate->getMail()
                    ->setReplyTo($sender['mail'], $sender['name']);
            } else {
                $mailTemplate->getMail()
                    ->addHeader('Reply-To', $sender['mail']);
            }
            $mailTemplate->setFromEmail($sender['mail']);
        }
        catch (Exception $ex) {
            
        }

        if (Mage::getStoreConfig(self::EMAIL_SENDER, $storeId) == '') {
            $senderMail = array(
                'name' => Mage::getStoreConfig('trans_email/ident_general/name'),
                'email' => Mage::getStoreConfig('trans_email/ident_general/email')
            );
        } else {
            $senderMail = Mage::getStoreConfig(self::EMAIL_SENDER, $storeId);
        }

        try {
            $mailTemplate
                ->setDesignConfig(array('area' => 'frontend', 'store' => $store))
                ->sendTransactional(
                    Mage::getStoreConfig(self::CUSTOMER_EMAIL_TEMPLATE, $storeId),
                                         $senderMail,
                                         $question->getCustomerEmail(), null,
                                         array(
                    'data' => $question,
                    //'dateAsked' => $dateAsked,
                    'product_url' => $product->getProductUrl(),
                    ), $storeId
            );
        }
        catch (Exception $e) {
            var_dump($e);
            die();
        }

        if (!$mailTemplate->getSentSuccess()) {
            throw new Exception($this->__('Message was successfully saved, but the email was not sent'));
        } else {
            $session = Mage::getSingleton('adminhtml/session');
            $session->addSuccess($this->__('Email was sent successfully'));
        }
    }

    public function sendAutorespondToCustomer($question)
    {
        $store = Mage::app()->getStore();
        $storeId = $store->getId();
        $mailTemplate = Mage::getModel('core/email_template');

        return;

        try {
            $sender = $this->getSender();
            if (method_exists($mailTemplate->getMail(), 'setReplyTo')) {
                $mailTemplate->getMail()->setReplyTo($sender['mail'],
                                                     $sender['name']);
            } else {
                $mailTemplate->getMail()->addHeader('Reply-To', $sender['mail']);
            }
            $mailTemplate->setFromEmail($sender['mail']);
        }
        catch (Exception $ex) {
            
        }

        $mailVariables = array(
            'data' => $question,
            'product_url' => $this->_product->getProductUrl(),
        );

        $mailTemplate
            ->setDesignConfig(
                array('area' => 'frontend', 'store' => $store)
            )
            ->sendTransactional(
                Mage::getStoreConfig('product_questions/autorespond/email_template'),
                                     Mage::getStoreConfig(self::EMAIL_SENDER),
                                                          $questionInfo['question_author_email'],
                                                          $questionInfo['question_author_name'],
                                                          $mailVariables,
                                                          $storeId
        );

        if (!$mailTemplate->getSentSuccess()) { //throw new Exception(); 
            $session->addError($this->__('An error occured, while sending a reply message to you.'));
        }
    }

}