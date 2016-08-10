<?php

namespace Ez\Util\Exception;

/**
 * Class AttributeNotRegisteredException
 *
 * @package Ez\Util\Exception
 * @author Derek Li
 */
class AttributeNotRegisteredException extends \Exception
{
    /**
     * The exception code.
     *
     * @var string
     */
    protected $code = '700001';

    /**
     * Override the message passed through.
     *
     * @param string $attrName Name of the attribute.
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($attrName, $code = 0, \Exception $previous = null)
    {
        parent::__construct(sprintf("The given attribute [%s] has not been registered.", $attrName), $code, $previous);
    }
}