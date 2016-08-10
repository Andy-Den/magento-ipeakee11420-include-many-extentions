<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoRichData
 */

class Amasty_SeoRichData_Model_Dom
{
    /** @var  DOMDocument $_document */
    protected $_document;
    /** @var  DOMXPath $_search */
    protected $_search;

    public function getDocument()
    {
        return $this->_document;
    }

    public function __construct($html)
    {
        $this->_document = new DOMDocument('1.0', 'UTF-8');
        //$this->_document->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD); // PHP >= 5.4

        if (function_exists('mb_convert_encoding')) // fallback
            $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

        $this->_document->formatOutput = true;
        @$this->_document->loadHTML($html);
        $this->_search = new DOMXPath($this->_document);
    }

    public function query($cssSelector)
    {
        $result = $this->_search->query(Zend_Dom_Query_Css2Xpath::transform($cssSelector));

        if ($result->length > 0)
            return $result->item(0);
        else
            return false;
    }

    public function queryAll($cssSelector)
    {
        return $this->_search->query(Zend_Dom_Query_Css2Xpath::transform($cssSelector));
    }

    public function save()
    {
        $html = preg_replace('|^.*\<body\>(.+)\<\/body\>.*$|is', '${1}', $this->_document->saveHTML()); // PHP < 5.4

        return $html;
    }

    public function appendElement($node, $name, array $attributes = array())
    {
        $newNode = $this->getDocument()->createElement($name);

        foreach ($attributes as $key => $value)
        {
            $newNode->setAttribute($key, $value);
        }

        $node->appendChild($newNode);
    }
}
