<?php

namespace Ez\Util;

use Ez\Util\Exception\AttributeNotRegisteredException;

/**
 * Class AttrAccessor
 * A container of attributes which are easier to set and get.
 *
 * @package Ez\Util
 * @author Derek Li
 */
class AttrAccessor
{
    /**
     * The collection of attributes.
     *
     * @var array
     */
    protected $attrsCollection = array();

    /**
     * The registered attributes.
     *
     * @var array
     */
    protected $registeredAttrs = array();

    /**
     * Construct.
     *
     * @param array $attributesToRegister
     */
    public function __construct(array $attributesToRegister = null)
    {
        if (isset($attributesToRegister)) {
            $this->registerAttrs($attributesToRegister);
        }
    }

    /**
     * Register the attributes.
     *
     * @param array $attributesToRegister
     * @return $this
     */
    public function registerAttrs($attributesToRegister)
    {
        foreach ($attributesToRegister as $attrName) {
            $this->registerAttr($attrName);
        }
        return $this;
    }

    /**
     * Register the attribute.
     *
     * @param string $attrName Name of the attribute.
     * @return $this
     */
    public function registerAttr($attrName)
    {
        if (!$this->isAttrRegistered($attrName)) {
            $this->registeredAttrs[] = $attrName;
        }
        return $this;
    }

    /**
     * If the given attribute is registered.
     *
     * @param string $attrName Name of the attribute.
     * @return bool
     */
    public function isAttrRegistered($attrName)
    {
        return in_array($attrName, $this->registeredAttrs);
    }

    /**
     * Check if the attribute has been accessed.
     *
     * @param string $attrName Name of the attribute.
     * @return bool
     */
    public function isAttrAccessed($attrName)
    {
        return array_key_exists($attrName, $this->attrsCollection);
    }

    /**
     * Check if the attribute is empty.
     *
     * @param string $attrName Name of the attribute.
     * @return bool
     * @throws AttributeNotRegisteredException
     */
    public function attrEmpty($attrName)
    {
        if (!$this->isAttrRegistered($attrName)) {
            throw new AttributeNotRegisteredException($attrName);
        }
        return !$this->isAttrAccessed($attrName) ||
            empty($this->attrsCollection[$attrName]);
    }

    /**
     * Check if the attribute is set.
     *
     * @param string $attrName Name of the attribute.
     * @return bool
     * @throws AttributeNotRegisteredException
     */
    public function attrIsSet($attrName)
    {
        if (!$this->isAttrRegistered($attrName)) {
            throw new AttributeNotRegisteredException($attrName);
        }
        return $this->isAttrAccessed($attrName) &&
            isset($this->attrsCollection[$attrName]);
    }

    /**
     * Set the value of the attribute.
     *
     * @param string $attrName
     * @param mixed $attrValue
     * @return $this
     * @throws AttributeNotRegisteredException
     */
    public function setAttr($attrName, $attrValue)
    {
        // Throw exception if the request attribute hasn't been registered.
        if ($this->isAttrRegistered($attrName)) {
            $this->attrsCollection[$attrName] = $attrValue;
            return $this;
        } else {
            throw new AttributeNotRegisteredException($attrName);
        }

    }

    /**
     * Get the value of the attribute.
     *
     * @param $attrName
     * @return mixed
     * @throws Exception\AttributeNotRegisteredException
     */
    public function getAttr($attrName)
    {
        if ($this->isAttrRegistered($attrName)) {
            if ($this->isAttrAccessed($attrName)) {
                return $this->attrsCollection[$attrName];
            }
        } else {
            throw new AttributeNotRegisteredException($attrName);
        }
    }

    /**
     * Get all the attributes.
     *
     * @return array
     */
    protected function getAttrsCollection()
    {
        return $this->attrsCollection;
    }

    /**
     * Get all the attributes as an array.
     *
     * @param array $attributeNames The attributes to get.
     * @return array
     */
    public function getAttrs(array $attributeNames = null)
    {
        if (!isset($attributeNames)) {
            return $this->getAttrsCollection();
        }
        $attributes = array();
        foreach ($attributeNames as $name) {
            $attributes[$name] = $this->getAttr($name);
        }
        return $attributes;
    }
}
