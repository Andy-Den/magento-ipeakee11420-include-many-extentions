<?php

/**
 * Class Balance_Router_Model_MatchResult_Abstract
 *
 * @author Derek Li
 */
abstract class Balance_Router_Model_MatchResult_Abstract implements Balance_Router_Model_MatchResult_Interface
{
    /**
     * @var bool
     */
    protected $_isMatched = true;

    /**
     * If the route is matched.
     *
     * @return bool
     */
    public function isMatched()
    {
        return $this->_isMatched;
    }
}