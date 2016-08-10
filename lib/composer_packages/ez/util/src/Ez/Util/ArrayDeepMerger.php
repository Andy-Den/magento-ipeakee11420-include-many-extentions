<?php

namespace Ez\Util;

/**
 * Class ArrayDeepMerger
 *
 * @package Ez\Util
 * @author Derek Li
 */
class ArrayDeepMerger
{
    /**
     * Recursively merge 2 arrays in a distinct way.
     *
     * @param array $array1 Array 1 to merge from.
     * @param array $array2 Array 2 to merge into array 1.
     * @return array
     */
    public function mergeDistinct(array $array1, array $array2)
    {
        $merged = $array1;
        foreach ($array2 as $key => $value) {
            if (is_array($value) &&
                isset($merged[$key]) &&
                is_array($merged[$key])
            ) {
                $merged[$key] = $this->mergeDistinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }
        return $merged;
    }
}