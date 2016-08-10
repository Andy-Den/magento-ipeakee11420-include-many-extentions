<?php

namespace Ez\DataStructure\Tests\KeyValueDataStorage\Key;

use Ez\DataStructure\KeyValueDataStorage\Key\JsonTags as JsonTagsKey;

class JsonTagsTest extends \PHPUnit_Framework_TestCase
{
    public function testToKey()
    {
        $jsonTagsKey = new JsonTagsKey(array(
            'a',
            'b',
            'c'
        ));
        $this->assertEquals('["a","b","c"]', $jsonTagsKey->toKey());
    }

    public function testToTags()
    {
        $jsonTagsKey = new JsonTagsKey('["a","b","c"]');
        $this->assertEquals(
            array(
                'a',
                'b',
                'c'
            ),
            $jsonTagsKey->toTags()
        );
    }

    public function testHasTags()
    {
        $jsonTagsKey = new JsonTagsKey('["a","b","c"]');
        $this->assertTrue($jsonTagsKey->hasTags(array('a')));
        $this->assertTrue($jsonTagsKey->hasTags(array('a', 'b')));
        $this->assertTrue($jsonTagsKey->hasTags(array('a', 'b', 'c')));
        $this->assertTrue($jsonTagsKey->hasTags(array('c', 'a', 'b')));
        $this->assertFalse($jsonTagsKey->hasTags(array('d')));
        $this->assertFalse($jsonTagsKey->hasTags(array('d', 'a', 'b')));
    }
}