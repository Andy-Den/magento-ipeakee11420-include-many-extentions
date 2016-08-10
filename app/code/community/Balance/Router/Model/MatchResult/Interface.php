<?php

/**
 * Interface Balance_Router_Model_MatchResult_Interface
 *
 * @author Derek Li
 */
interface Balance_Router_Model_MatchResult_Interface
{
    /**
     * If the route is matched.
     *
     * @return bool
     */
    public function isMatched();
}