<?php

namespace Ez\Util;

/**
 * Class AttrAccess
 * @package Ez\Util
 * @author Derek Li
 */
abstract class AttrAccessAbstract
{
    /**
     * @var AttrAccessor
     */
    protected $attrAccessor = null;

    /**
     * Here is where the magic calls are triggered.
     * Every undefined method named using '_' at the beginning will be caught here.
     *
     * @param string $method
     * @param mixed $args
     * @return $this|mixed
     * @throws \Exception
     */
    public function __call($method, $args)
    {
        if (preg_match('/^_([a-zA-Z1-9]+)_$/', $method, $matches)) {
            if (count($args) > 0) {
                $this->getAttrAccessor()->setAttr($matches[1], $args[0]);
                return $this;
            } else {
                return $this->getAttrAccessor()->getAttr($matches[1]);
            }
        }
        elseif (preg_match('/^_([a-zA-Z1-9]+)Empty$/', $method, $matches)) {
            return $this->getAttrAccessor()->attrEmpty($matches[1]);
        }
        elseif (preg_match('/^_([a-zA-Z1-9]+)IsSet$/', $method, $matches)) {
            return $this->getAttrAccessor()->attrIsSet($matches[1]);
        } else {
            throw new \Exception(sprintf('Method "%s" does not exist. Called by class "%s".', $method, get_called_class()));
        }
    }

    /**
     * Get the attribute accessor.
     *
     * @return AttrAccessor
     */
    public function getAttrAccessor()
    {
        if (!isset($this->attrAccessor)) {
            $this->attrAccessor = new AttrAccessor();
            $this->registerAttrs($this->attrAccessor);
        }
        return $this->attrAccessor;
    }

    /**
     * Register attributes (instance variables) of the class.
     *
     * @param AttrAccessor $attrAccessor
     */
    protected function registerAttrs(AttrAccessor $attrAccessor)
    {
        $attrAccessor->registerAttrs($this->getAttrsToRegister());
    }

    /**
     * The method should return an array with all the attributes (instance variables) needed by the class.
     * The reason why we don't do this in a invisible (automatic) way is to make it more obvious to
     * see all the instance variables of the class which will be easier to read and maintain.
     *
     * @return array Attrs (instance variables) of the class.
     */
    abstract protected function getAttrsToRegister();
}