<?php

namespace Ez\Util\Tests;

use Ez\Util\AttrAccessor;

/**
 * Class AttrAccessorTest
 * @package Balance\MageWatchBundle\Tests\Outside\Ez\Util
 * @author Derek Li
 */
class AttrAccessorTest extends \PHPUnit_Framework_TestCase
{
    public function attrAccessorProvider()
    {
        $attrAccessor = new AttrAccessor();
        $attrAccessor->registerAttrs(array('name', 'age', 'address'));
        return array(array($attrAccessor));
    }

    /**
     * @dataProvider attrAccessorProvider
     */
    public function testIsAttrRegistered($attrAccessor)
    {
        $this->assertTrue($attrAccessor->isAttrRegistered('name'));
        $this->assertFalse($attrAccessor->isAttrRegistered('none registered attribute'));
    }

    /**
     * @dataProvider attrAccessorProvider
     * @expectedException \Ez\Util\Exception\AttributeNotRegisteredException
     */
    public function testIsAttrAccessed($attrAccessor)
    {
        $this->assertFalse($attrAccessor->isAttrAccessed('name'));
        $attrAccessor->setAttr('name', 'my name');
        $this->assertTrue($attrAccessor->isAttrAccessed('name'));
        $attrAccessor->getAttr('age');
        $this->assertFalse($attrAccessor->isAttrAccessed('age'));
        $attrAccessor->getAttr('address');
        $this->assertFalse($attrAccessor->isAttrAccessed('address'));
    }

    /**
     * @dataProvider attrAccessorProvider
     * @expectedException \Ez\Util\Exception\AttributeNotRegisteredException
     */
    public function testSetAttr($attrAccessor)
    {
        $attrAccessor->setAttr('name', 'my name');
        $attrAccessor->setAttr('none registered attribute', 'what ever');
        $this->assertEquals('my name', $attrAccessor->getAttr('name'));
    }

    /**
     * @dataProvider attrAccessorProvider
     * @expectedException \Ez\Util\Exception\AttributeNotRegisteredException
     */
    public function testGetAttr($attrAccessor)
    {
        $this->assertNull($attrAccessor->getAttr('address'));
        $this->assertEquals('what ever', $attrAccessor->getAttr('none registered attribute'));
    }

    /**
     * @dataProvider attrAccessorProvider
     * @expectedException \Ez\Util\Exception\AttributeNotRegisteredException
     */
    public function testAttrAccessorAttrEmpty($attrAccessor)
    {
        $attrAccessor->setAttr('name', 'derek');
        $this->assertFalse($attrAccessor->attrEmpty('name'));
        $this->assertTrue($attrAccessor->attrEmpty('age'));
        $this->assertTrue($attrAccessor->attrEmpty('notRegisteredAttribute'));
    }

    /**
     * @dataProvider attrAccessorProvider
     */
    public function testAttrAccessorAttrIsSet($attrAccessor)
    {
        $attrAccessor->setAttr('name', 'derek');
        $this->assertTrue($attrAccessor->attrIsSet('name'));
        $this->assertFalse($attrAccessor->attrIsSet('age'));
    }

    /**
     * @dataProvider attrAccessorProvider
     * @expectedException \Ez\Util\Exception\AttributeNotRegisteredException
     */
    public function testAttrAccessorAttrIsSetException($attrAccessor)
    {
        $this->assertTrue($attrAccessor->attrIsSet('notRegisteredAttribute'));
    }
}

