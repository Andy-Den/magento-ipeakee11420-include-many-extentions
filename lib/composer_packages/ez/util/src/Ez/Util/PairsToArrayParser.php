<?php

namespace Ez\Util;

/**
 * Class PairsToArrayParser
 *
 * @package Ez\Util
 * @author Derek Li
 */
class PairsToArrayParser
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
     * @param array $pairs OPTIONAL The original array.
     * @param string $delimiter OPTIONAL The delimiter to use in the keys in pairs.
     */
    public function __construct(array $pairs = array(), $delimiter = null)
    {
        if (isset($pairs)) {
            $this->setPairs($pairs);
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

    /**
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * @param $pairs
     * @return $this
     */
    public function setPairs($pairs)
    {
        $this->pairs = $pairs;
        return $this;
    }

    /**
     * @return array
     */
    public function getPairs()
    {
        return $this->pairs;
    }

    /**
     * @return array
     */
    public function getArray()
    {
        if (empty($this->array) && !empty($this->pairs)) {
            $this->toArray();
        }
        return $this->array;
    }

    /**
     * Convert the paris into array.
     */
    protected function toArray()
    {
        $this->iterate($this->getPairs());
    }

    public function refresh()
    {
        $this->toArray();
        return $this;
    }

    /**
     * Iterate the key-value pairs.
     *
     * @param array $pairs Key-value pairs.
     */
    protected function iterate(array $pairs)
    {
        foreach ($pairs as $key => $val) {
            $this->addToArray($this->array, explode($this->getDelimiter(), $key), $val);
        }
    }

    /**
     * Recursively add value to the right position in the array.
     *
     * @param array $array Array to add.
     * @param array $pathNodes Path nodes.
     * @param mixed $val The value to add.
     */
    protected function addToArray(array &$array, array $pathNodes, $val)
    {
        $key = array_shift($pathNodes);
        if (count($pathNodes) > 0) {
            if (!is_array($array[$key])) {
                $array[$key] = array();
            }
            $this->addToArray($array[$key], $pathNodes, $val);
        } else {
            $array[$key] = $val;
        }
    }
}