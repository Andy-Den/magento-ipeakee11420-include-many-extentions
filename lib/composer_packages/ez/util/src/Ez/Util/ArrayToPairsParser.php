<?php

namespace Ez\Util;

/**
 * Class ArrayToPairsParser
 *
 * @package Ez\Util
 * @author Derek Li
 */
class ArrayToPairsParser
{
    /**
     * Delimiter used in the keys of pairs.
     *
     * @var string
     */
    protected $delimiter = '.';

    /**
     * The original array.
     *
     * @var array
     */
    protected $array = array();

    /**
     * Key-value pairs.
     *
     * @var array
     */
    protected $pairs = array();

    /**
     * Nodes in the path.
     *
     * @var array
     */
    protected $pathNodes = array();

    /**
     *
     * @param array $array OPTIONAL The original array.
     * @param string $delimiter OPTIONAL The delimiter to use in the keys in pairs.
     */
    public function __construct(array $array = array(), $delimiter = null)
    {
        if (isset($array)) {
            $this->setArray($array);
        }
        if (isset($delimiter)) {
            $this->setDelimiter($delimiter);
        }
    }

    /**
     * @param $delimiter
     * @return $this
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
        return $this;
    }

    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * @param $array
     * @return $this
     */
    public function setArray($array)
    {
        $this->array = $array;
        return $this;
    }

    /**
     * @return array
     */
    public function getArray()
    {
        return $this->array;
    }

    /**
     * @return array
     */
    public function getPairs()
    {
        if (empty($this->pairs) && !empty($this->array)) {
            $this->toPairs();
        }
        return $this->pairs;
    }

    /**
     * Convert the array into paris.
     */
    protected function toPairs()
    {
        $this->iterate($this->getArray());
    }

    /**
     * @return $this
     */
    public function refresh()
    {
        $this->toPairs();
        return $this;
    }

    /**
     * Recursively traverse the array and generate the key-value pairs.
     *
     * @param array $array
     * @param array $pathNodes
     */
    protected function iterate(array $array, array $pathNodes = array())
    {
        foreach ($array as $key => $val) {
            $pathNodes[] = $key;
            if (is_array($val) && count($val) > 0) {
                $this->iterate($val, $pathNodes);
            } else {
                $this->pairs[implode($this->getDelimiter(), $pathNodes)] = $val;
            }
            array_pop($pathNodes);
        }
    }
}