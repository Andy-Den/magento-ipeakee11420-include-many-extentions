<?php

/**
 * Class Balance_Router_Model_MatchResult_Redirect
 *
 * @author Derek Li
 */
class Balance_Router_Model_MatchResult_Redirect extends Balance_Router_Model_MatchResult_Positive
{
    /**
     * The http header code for redirection.
     *
     * @var int
     */
    protected $_headerCode = 301;

    /**
     * The url to redirect to.
     *
     * @var string
     */
    protected $_toUrl = null;

    /**
     * @param int $code
     * @return $this
     */
    public function setHeaderCode($code)
    {
        $this->_headerCode = $code;
        return $this;
    }

    /**
     * @return int
     */
    public function getHeaderCode()
    {
        return $this->_headerCode;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setToUrl($url)
    {
        $this->_toUrl = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getToUrl()
    {
        return $this->_toUrl;
    }
}