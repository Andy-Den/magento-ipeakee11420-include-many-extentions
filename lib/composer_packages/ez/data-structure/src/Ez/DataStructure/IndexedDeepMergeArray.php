<?php

namespace Ez\DataStructure;

use Ez\Util\ArrayDeepMerger;

/**
 * Class IndexedArray
 *
 * @package Ez\DataStructure
 * @author Derek Li
 */
class IndexedDeepMergeArray extends IndexedArray
{
    /**
     * @var ArrayDeepMerger
     */
    protected $arrayDeepMerger = null;

    /**
     * Each array can be passed through as many as needed
     * and will be recursively merged together.
     *
     */
    public function __construct()
    {
        // Take over whatever array passed and merge them in sequence.
        $args = func_get_args();
        if (count($args) > 0) {
            foreach ($args as $a) {
                if (is_array($a)) {
                    $this->array = $this->getArrayDeepMerger()->mergeDistinct($this->array, $a);
                }
            }
        }
    }

    /**
     * Set the array deep merger.
     *
     * @param ArrayDeepMerger $arrayDeepMerger
     * @return $this
     */
    public function setArrayDeepMerger(ArrayDeepMerger $arrayDeepMerger)
    {
        $this->arrayDeepMerger = $arrayDeepMerger;
        return $this;
    }

    /**
     * Get the array deep merger.
     *
     * @return ArrayDeepMerger
     */
    public function getArrayDeepMerger()
    {
        if (!isset($this->arrayDeepMerger)) {
            $this->arrayDeepMerger = new ArrayDeepMerger();
        }
        return $this->arrayDeepMerger;
    }

    /**
     * @param array $array
     * @return $this
     */
    public function addArray(array $array)
    {
        $this->array = $this->getArrayDeepMerger()->mergeDistinct($this->array, $array);
        return $this;
    }
}