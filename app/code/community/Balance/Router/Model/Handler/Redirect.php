<?php

/**
 * Class Balance_Router_Model_Handler_Redirect
 *
 * @author Derek Li
 */
class Balance_Router_Model_Handler_Redirect implements Balance_Router_Model_Handler_Interface
{
    /**
     * Handle the match result.
     *
     * @param Balance_Router_Model_MatchResult_Interface $result
     * @return mixed
     */
    public function handle(Balance_Router_Model_MatchResult_Interface $result)
    {
        if ($result instanceof Balance_Router_Model_MatchResult_Redirect) {
            Mage::app()->getResponse()
                ->setRedirect($result->getToUrl(), $result->getHeaderCode())
                ->sendResponse();
        }
    }
}