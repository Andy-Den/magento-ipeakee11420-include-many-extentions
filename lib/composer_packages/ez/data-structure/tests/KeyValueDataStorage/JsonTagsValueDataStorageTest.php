<?php

namespace Ez\DataStructure\Tests\KeyValueDataStorage;

use Ez\DataStructure\KeyValueDataStorage\JsonTagsValueDataStorage;
use Ez\DataStructure\KeyValueDataStorage\Key\JsonTags as JsonTagsKey;

/**
 * Class IndexedArrayTest
 *
 * @package Ez\DataStructure\Tests\KeyValueDataStorage
 * @author Derek Li
 */
class JsonTagsValueDataStorageTest extends \PHPUnit_Framework_TestCase
{
    public function dataProvider()
    {
        $dataToSet = array(
            'data1' => array(
                'key' => 'key1',
                'value' => 'val1'
            ),
            'data2' => array(
                'key' => array(
                    'a',
                    'b',
                    'c'
                ),
                'value' => 'val2'
            ),
            'data3' => array(
                'key' => new JsonTagsKey(array(
                        'n',
                        'm',
                        'k',
                        'a',
                        'c'
                    )),
                'value' => 'val3'
            )
        );
        $dataSet = array(
            '["key1"]' => 'val1',
            '["a","b","c"]' => 'val2',
            '["a","c","k","m","n"]' => 'val3'
        );

        return array(array($dataToSet, $dataSet));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testSetGet($dataToSet,$dataSet)
    {
        $dataStorage = new JsonTagsValueDataStorage();
        foreach ($dataToSet as $d) {
            $dataStorage->set($d['key'], $d['value']);
        }
        $this->assertEquals($dataSet, $dataStorage->get());
        $this->assertEquals('val1', $dataStorage->get('key1'));
        $this->assertEquals(
            'val2',
            $dataStorage->get(new JsonTagsKey(array(
                'a',
                'b',
                'c'
            )))
        );
        $this->assertEquals(
            'val3',
            $dataStorage->get(array(
                'n',
                'm',
                'k',
                'a',
                'c'
            ))
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testFind($dataToSet)
    {
        $dataStorage = new JsonTagsValueDataStorage();
        foreach ($dataToSet as $d) {
            $dataStorage->set($d['key'], $d['value']);
        }
        $expectToFind = array(
            '["a","b","c"]' => 'val2',
            '["a","c","k","m","n"]' => 'val3'
        );
        $this->assertEquals($expectToFind, $dataStorage->find(array('c', 'a')));
    }
}
