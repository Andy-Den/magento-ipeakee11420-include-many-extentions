<?php

/**
 * Interface Balance_Router_Model_Handler_Interface
 *
 * @author Derek Li
 */
interface Balance_Router_Model_Handler_Interface
{
    /**
     * Handle the route result.
     *
     * @param Balance_Router_Model_MatchResult_Interface $result
     * @return mixed
     */
    public function handle(Balance_Router_Model_MatchResult_Interface $result);
}