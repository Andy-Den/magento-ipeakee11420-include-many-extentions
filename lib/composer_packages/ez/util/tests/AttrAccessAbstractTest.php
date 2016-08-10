<?php

namespace Ez\Util\Tests;

use Ez\Util\AttrAccessAbstract;

/**
 * Class AttrAccessAbstractTest
 * @package Ez\Util\Tests
 * @author Derek Li
 */
class AttrAccessAbstractTest extends \PHPUnit_Framework_TestCase
{
    public function attrAccessProvider()
    {
        $attrAccess = new AttrAccess();
        return array(array($attrAccess));
    }

    /**
     * @dataProvider attrAccessProvider
     */
    public function testMagicSet($attrAccess)
    {
        $attrAccess->_name_('derek');
        $this->assertEquals('derek', $attrAccess->getAttrAccessor()->getAttr('name'));
    }

    /**
     * @dataProvider attrAccessProvider
     */
    public function testMagicGet($attrAccess)
    {
        $attrAccess->_name_('derek');
        $this->assertEquals('derek', $attrAccess->_name_());
    }

    /**
     * @dataProvider attrAccessProvider
     */
    public function testMagicEmpty($attrAccess)
    {
        $attrAccess->_name1_(null);
        $attrAccess->_name2_('');
        $attrAccess->_name3_(array());
        $attrAccess->_name4_(1);
        $this->assertTrue($attrAccess->_nameEmpty());
        $this->assertTrue($attrAccess->_name1Empty());
        $this->assertTrue($attrAccess->_name2Empty());
        $this->assertTrue($attrAccess->_name3Empty());
        $this->assertFalse($attrAccess->_name4Empty());
    }

    /**
     * @dataProvider attrAccessProvider
     */
    public function testMagicIsSet($attrAccess)
    {
        $attrAccess->_name1_(null);
        $attrAccess->_name2_('');
        $attrAccess->_name3_(array());
        $attrAccess->_name4_(1);
        $this->assertFalse($attrAccess->_nameIsSet());
        $this->assertFalse($attrAccess->_name1IsSet());
        $this->assertTrue($attrAccess->_name2IsSet());
        $this->assertTrue($attrAccess->_name3IsSet());
        $this->assertTrue($attrAccess->_name4IsSet());
    }
}

class AttrAccess extends AttrAccessAbstract
{
    public function getAttrsToRegister()
    {
        return array(
            'name',
            'name1',
            'name2',
            'name3',
            'name4',
            'age',
            'billingAddress',
            'shippingAddress'
        );
    }
}


