<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoRichData
 */

class Amasty_SeoRichData_Helper_HtmlManager extends Mage_Core_Helper_Abstract
{
    const ITEM_TYPE_PRODUCT_URL      = 'https://schema.org/Product';

    protected $_customTag = 'amasty_seo';

    protected $_html;
    protected $_type;

    public function apply($html, $types = array('product'))
    {
        $this->_html = $html;

        foreach ($types as $type)
        {
            if (Mage::getStoreConfig('amseorichdata/' . $type .'/enabled'))
            {
                $methodName = '_add' . ucfirst(strtolower($type)) . 'Data';
                if (! method_exists($this, $methodName)) {
                    throw new Exception("Method $methodName is not implemented");
                }

                $this->_type = $type;
                $this->$methodName();
            }
        }

        return $this->_html;
    }

    protected function _addProductData()
    {
        $request = Mage::app()->getRequest();
        if ($request->getControllerName() == 'product' && $request->getActionName() == 'view')
        {
            $this->_addItemScope('container', self::ITEM_TYPE_PRODUCT_URL);

            $this
                ->_addProp('description')
                ->_addProp('name')
            ;
        }
    }

    protected function _addProp($type)
    {
        $selector = $this->_selector($type);

        $this->_addTagToHtml($selector, 'itemprop', $type);

        return $this;
    }

    /**
     * @param $cssSelector
     * @param $tagName
     * @param string $tagValue
     *
     * @return bool
     */
    protected function _addTagToHtml($cssSelector, $attributes, $value = '')
    {
        $rexExp = $this->getRegExp($cssSelector, $matchNum);

        if ($rexExp == '/') {
            return false;
        }

        if (!is_array($attributes))
        {
            $attributes = array($attributes => $value);
        }

        $attributesHtml = '';

        foreach ($attributes as $name => $value)
        {
            $value = strip_tags($value);
            $attributesHtml .= ' ' . strip_tags($name)
                . (! empty($value) ? '="' . $value . '"' : '');
        }

        $firstMatch    = $matchNum ? "\${1}\${" . ($matchNum * 2 + 2) . "} " : " \${1} ";
        $secondMatch   = $matchNum ? "\${" . ($matchNum * 2 + 5) . "} " : " \${4} ";
        $replaceString = $firstMatch
            . $attributesHtml
            . $secondMatch;

        if ($html = preg_replace($rexExp, $replaceString, $this->_html, 1, $count))
        {
            $this->_html = $html;
        }

        if ($count < 1)
            return false;

        return true;
    }

    /**
     * Add scope tag to the container
     * Example:
     * <div containerClass itemscope="" itemtype="http://data-vocabulary.org/Product" />
     *
     * @param $block
     * @param $dictionaryUrl
     * @param array $additionalAttributes
     */
    protected function _addItemScope($block, $dictionaryUrl, $additionalAttributes = array())
    {
        $selector = $this->_selector($block);
        $attributes = array(
            'itemscope' => '',
            'itemtype'  => $dictionaryUrl
        );

        $attributes = $attributes + $additionalAttributes;

        $this->_addTagToHtml($selector, $attributes);
    }

    protected function _selector($block)
    {
        return Mage::getStoreConfig('amseorichdata/' . $this->_type . '/' . $block . '_selector');
    }

    /**
     * Get regexp for select html element
     *
     * @param $cssSelector
     * @param $matchNum
     * @param string $additional
     *
     * @return string
     */
    public function getRegExp($cssSelector, &$matchNum)
    {
        $selectorChain = preg_split('/\s+/', $cssSelector);
        $rexExp        = '/';
        $matchNum      = 0;
        for ($i = 0; $i < count($selectorChain); $i ++) {
            $selectorTag   = substr($selectorChain[$i], 0, 1);
            $selectorClass = substr($selectorChain[$i], 1, strlen($selectorChain[$i]));

            $selectorName = null;
            switch ($selectorTag) {
                case '.' :
                    $selectorName = 'class';
                    break;

                case '#' :
                    $selectorName = 'id';
                    break;

                default :
                    $selectorName  = null;
                    $selectorClass = $selectorChain[$i];
                    break;
            }

            $selectorClass = preg_quote($selectorClass);

            if ($i != count($selectorChain) - 1) {
                if (! $matchNum) {
                    $rexExp .= '(';
                }

                $matchNum ++;
                if ($selectorName) {
                    $rexExp .= "\<\w+.[^>]*?$selectorName=['|\"]((?=.).*?\s|){$selectorClass}((?=.)\s.*?|)[\s\S]*?";
                } else {
                    //add unnecessary brackets for keep numbers of replacement
                    $rexExp .= "\<{$selectorClass}((?=.)\s.*?|)[\s\S]*?(.)*?";
                }
            } else {
                if ($matchNum) {
                    $rexExp .= ')';
                }

                if ($selectorName) {
//                    $rexExp = "/(<[^>]+\s*$selectorName\s*=\s*['\"]?{$selectorClass}['\"]?)([^>]*>)/i";
                    //oldVersion
                    $rexExp .= "(\<\w+[^>]*?$selectorName=['|\"]((?=.).*?\s|){$selectorClass}((?=.)\s.*?|)['|\"][\s\S]*?)(\/?\>)/i";
                } else {
                    //add unnecessary brackets for keep numbers of replacement
                    $rexExp .= "(\<{$selectorClass}((?=.)\s.*?|)[\s\S]*?(.)*?)(\/?\>)/i";
                }
            }
        }

        return $rexExp;
    }

    public function getCustomTag()
    {
        return $this->_customTag;
    }
}
