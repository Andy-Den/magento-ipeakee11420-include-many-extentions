<?php

namespace Ez\Util\Tests;

use Ez\Util\ArrayDeepMerger;

/**
 * Class ArrayDeepMergerTest
 *
 * @package Ez\Util\Tests
 * @author Derek Li
 */
class ArrayDeepMergerTest extends \PHPUnit_Framework_TestCase
{
    public function testMergeDistinct()
    {
        $array1 = array(
            'a1' => 'b1',
            'a2' => array(
                'a21' => 'b21',
                'a22' => array(
                    'b221',
                    'b222',
                    'a221' => array(
                        'b2211'
                    )
                )
            ),
            'a3' => array(
                'b31',
                'b32'
            ),
        );
        $array2 = array(
            'a1' => 'c1',
            'a2' => array(
                'a22' => array(
                    'a221' => array(
                        'b2211'
                    ),
                    'c221'
                )
            ),
            'a3' => array(
                'b31',
                'c32' => array(
                    'c321',
                    'c322'
                )
            ),
            'a6' => 'b6'
        );
        $array3 = array(
            'a1' => 'c3',
            'a2' => array(
                'a22' => array(
                    'd221'
                )
            ),
            'a3' => array(
                'c32' => array(
                    'c321',
                    'c322'
                )
            ),
            'a4' => 'c4'
        );
        $mergedArray = array(
            'a1' => 'c3',
            'a2' => array(
                'a21' => 'b21',
                'a22' => array(
                    'd221',
                    'b222',
                    'a221' => array(
                        'b2211'
                    )
                )
            ),
            'a3' => array(
                'b31',
                'b32',
                'c32' => array(
                    'c321',
                    'c322'
                )
            ),
            'a6' => 'b6',
            'a4' => 'c4'
        );
        $arrayDeepMerger = new ArrayDeepMerger();
        $this->assertEquals($mergedArray, $arrayDeepMerger->mergeDistinct($arrayDeepMerger->mergeDistinct($array1, $array2), $array3));
    }
}


