<?php

namespace Ez\DataStructure\Tests;

use Ez\DataStructure\IndexedDeepMergeArray;

/**
 * Class IndexedDeepMergeArrayTest
 *
 * @package Balance\MageWatchBundle\Tests\Outside\Ez\DataStructure
 * @author Derek Li
 */
class IndexedDeepMergeArrayTest extends \PHPUnit_Framework_TestCase
{
    public function indexedArrayProvider()
    {
        $array = array(
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
        $attrAccessor = new IndexedDeepMergeArray($array);
        return array(array($attrAccessor, $array));
    }

    /**
     * @dataProvider indexedArrayProvider
     */
    public function testGet(IndexedDeepMergeArray $indexedArray, array $array)
    {
        $this->assertEquals($array, $indexedArray->getArray());
        $this->assertEquals($array, $indexedArray->get(''));
        $this->assertEquals($array, $indexedArray->get('/'));
        $this->assertEquals($array['a1'], $indexedArray->get('a1'));
        $this->assertEquals($array['a2']['a21'], $indexedArray->get('a2/a21'));
        $this->assertEquals($array['a2']['a22'], $indexedArray->get('a2/a22'));
        $this->assertEquals($array['a2']['a22']['a221'], $indexedArray->get('a2/a22/a221'));
    }

    /**
     * @dataProvider indexedArrayProvider
     * @expectedException \Ez\DataStructure\Exception\IndexNotExistException
     */
    public function testGetException(IndexedDeepMergeArray $indexedArray)
    {
        $indexedArray->get('c');
    }

    /**
     *
     */
    public function testMerge()
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
        $attrAccessor1 = new IndexedDeepMergeArray($array1, $array2, $array3);
        $attrAccessor2 = new IndexedDeepMergeArray($array1, $array2);
        $attrAccessor2->addArray($array3);
        $attrAccessor3 = new IndexedDeepMergeArray();
        $attrAccessor3
            ->addArray($array1)
            ->addArray($array2)
            ->addArray($array3)
            ->addArray(array());
        $this->assertEquals($mergedArray, $attrAccessor1->getArray());
        $this->assertEquals($attrAccessor1->getArray(), $attrAccessor2->getArray());
        $this->assertEquals($attrAccessor2->getArray(), $attrAccessor3->getArray());
    }
}
