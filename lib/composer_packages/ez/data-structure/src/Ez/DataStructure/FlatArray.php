<?php

namespace Ez\DataStructure;

use Ez\Util\ArrayToPairsParser;
use Ez\Util\PairsToArrayParser;

/**
 * Class FlatArray
 *
 * @package Ez\DataStructure
 * @author Derek Li
 */
class FlatArray
{
    /**
     * @var ArrayToPairsParser
     */
    protected $arrayToPairsParser = null;

    /**
     * @var PairsToArrayParser
     */
    protected $pairsToArrayParser = null;

    /**
     * @var array
     */
    protected $array = array();

    /**
     * @var array
     */
    protected $pairs = array();

    /**
     * @param array $array
     */
    public function __construct(array $array = null)
    {
        if (isset($array)) {
            $this
                ->setArray($array)
                ->setPairs($array);
        }
    }

    /**
     * Set array to pairs parser.
     *
     * @param ArrayToPairsParser $arrayToPairsParser
     * @return $this
     */
    public function setArrayToPairsParser(ArrayToPairsParser $arrayToPairsParser)
    {
        $this->arrayToPairsParser = $arrayToPairsParser;
        return $this;
    }

    /**
     * Get array to pairs parser.
     *
     * @return ArrayToPairsParser
     */
    public function getArrayToPairsParser()
    {
        if (!isset($this->arrayToPairsParser)) {
            $this->arrayToPairsParser = new ArrayToPairsParser();
        }
        return $this->arrayToPairsParser;
    }

    /**
     * Set pairs to array parser.
     *
     * @param PairsToArrayParser $pairsToArrayParser
     * @return $this
     */
    public function setPairsToArrayParser(PairsToArrayParser $pairsToArrayParser)
    {
        $this->pairsToArrayParser = $pairsToArrayParser;
        return $this;
    }

    /**
     * Get pairs to array parser.
     *
     * @return PairsToArrayParser
     */
    public function getPairsToArrayParser()
    {
        if (!isset($this->pairsToArrayParser)) {
            $this->pairsToArrayParser = new PairsToArrayParser();
        }
        return $this->pairsToArrayParser;
    }

    /**
     * Set the array to parse.
     *
     * @param array $array
     * @return $this
     */
    public function setArray(array $array)
    {
        $this->array = $array;
        $this->clearPairs();
        return $this;
    }

    /**
     * Clear the array.
     *
     * @return $this
     */
    public function clearArray()
    {
        $this->array = array();
        return $this;
    }

    /**
     * Get the parsed array.
     *
     * @return array
     */
    public function getArray()
    {
        return $this->toArray();
    }

    /**
     * Set pairs to parse.
     *
     * @param array $pairs
     * @return $this
     */
    public function setPairs(array $pairs)
    {
        $this->pairs = $pairs;
        $this->clearArray();
        return $this;
    }

    /**
     * Clear the pairs.
     *
     * @return $this
     */
    public function clearPairs()
    {
        $this->pairs = array();
        return $this;
    }

    /**
     * Get parsed pairs.
     *
     * @return array
     */
    public function getPairs()
    {
        return $this->toPairs();
    }

    /**
     * Parse the pairs to array.
     *
     * @return array
     */
    public function toArray()
    {
        if (empty($this->pairs)) {
            return array();
        }
        if (empty($this->array)) {
            $this->array = $this
                ->getPairsToArrayParser()
                ->setPairs($this->pairs)
                ->getArray();
        }
        return $this->array;
    }

    /**
     * Parse the array to pairs.
     *
     * @return array
     */
    public function toPairs()
    {
        if (empty($this->array)) {
            return array();
        }
        if (empty($this->pairs)) {
            $this->pairs = $this
                ->getArrayToPairsParser()
                ->setArray($this->array)
                ->getPairs();
        }
        return $this->pairs;
    }
}