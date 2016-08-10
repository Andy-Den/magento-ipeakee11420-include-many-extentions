<?php

class Innobyte_ProductQuestions_IndexController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function addAction()
    {
        $request = $this->getRequest();
        $html = '<div align="center" style="margin:20px 0 0 0">
                    <a style="cursor:pointer" hreh="javascript:void(0)" onclick="inno.product_questions.hideQuestionForm()">' . $this->__('Close window') . '</a>
                    <div class="clearer"></div>
                <div>';
        $msg = '';
        if ($request->isPost()) {
            try {
                $data = $request->getPost();
                $data['created'] = Varien_Date::now();
                $data['store_id'] = Mage::app()->getStore()->getId();
                $question = Mage::getModel('innobyte_product_questions/question')
                    ->setData($data)
                    ->save();

                $msg = $this->__('Question added');
                $helper = Mage::helper('innobyte_product_questions');
                if (Mage::getStoreConfig(Innobyte_ProductQuestions_Helper_Data::EMAIL_RECIPIENT)) {
                    // send email to admin about new question
                    $senderEmail = Mage::getStoreConfig(Innobyte_ProductQuestions_Helper_Data::EMAIL_RECIPIENT);
                    $helper->sendEmailToAdmin($question, $senderEmail);
                }

                // sending a reply to customer
                if (Mage::getStoreConfig('productquestions/autorespond/status')) {
                    $helper->sendAutorespondToCustomer($question);
                }
            }
            catch (Exception $e) {
                var_dump($e->getMessage());
            }
        }

        $result = array('message' => $msg, 'html' => $html);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($result));
    }

    /**
     * save vote to database
     * @return json 
     */
    public function voteAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            die('Not allowed');
        }

        $response = array();
        $request = $this->getRequest();
        if ( ! $request->isPost() ) {
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(json_encode($response));
            return;
        }

        try {
            // set data to save
            $data['question_id'] = $request->getParam('id');
            $data['user_id'] = (int) Mage::getSingleton('customer/session')->getCustomer()->getId();
            $data['vote'] = $request->getParam('vote');
            $data['vote_date'] = Varien_Date::now();
            $data['ip'] = '';

            // set cookie if customer not logged in
            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                $data['ip'] = long2ip(Mage::helper('core/http')->getRemoteAddr(true));
                $cookie = Mage::getSingleton('core/cookie');
                $cookie->set('question_vote_' . $request->getParam('id'),
                        $request->getParam('id'),
                        time() + 86400,
                        '/');
            }

            $vote = Mage::getModel('innobyte_product_questions/votes');
            $vote->setData($data);
            $vote->save();

            $model = Mage::getModel('innobyte_product_questions/question')
                    ->load($request->getParam('id'));

            $votes = $model->getData('votes');
            // update question "votes" field
            $model->setVotes($votes + $request->getParam('vote'));
            $model->save();

            // set response
            $response['message'] = $this->__('Your vote was submitted');
            $response['response'] = 'success';
            $response['text'] = ($request->getParam('vote') == 1 ? $this->__('You think this question is helpful') : $this->__('You think this question is not helpful'));
        } catch (Exception $e) {
            $response['message'] = $this->__('Could not submit vote. Please try again later');
            $response['response'] = 'error';

            // remove cookie on errors
            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                $cookie = Mage::getSingleton('core/cookie');
                $cookie->delete('question_vote_' . $request->getParam('id'));
            }
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($response));
    }

    
    public function reloadAction()
    {

        // check if is ajax request
        if (!$this->getRequest()->isXmlHttpRequest()) {
            die('Not allowed');
        }

        $request = $this->getRequest();

        // Get current store id 
        $storeId = Mage::app()->getStore()->getId();
        $productId = $request->getParam('product');

        $productObj = Mage::getModel('catalog/product')->load($productId);
        Mage::register('product', $productObj);

        $sortBy = $request->getParam('sortby');
        $sortDir = $request->getParam('sort');
        $limitMultiplier = $request->getParam('limit');

        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->load('catalog_product_view');
        $layout->generateXml()->generateBlocks();

        $block = $layout->getBlock('inno.product_questions_list');
        $block->setFilters($sortBy, $sortDir, $limitMultiplier);
        $html = $block->setTemplate('innobyte/product_questions/list.phtml')->toHtml();

        echo $html;
    }

}
