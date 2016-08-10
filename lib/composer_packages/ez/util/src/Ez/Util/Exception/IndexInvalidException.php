<?php

namespace Ez\Util\Exception;

/**
 * Class IndexInvalidException
 *
 * @package Ez\Util\Exception
 * @author Derek Li
 */
class IndexInvalidException extends \Exception
{
    /**
     * The exception code.
     *
     * @var string
     */
    protected $code = '700002';

    /**
     * Override the message passed through.
     *
     * @param string $index Name of the attribute.
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($index, $code = 0, \Exception $previous = null)
    {
        parent::__construct(sprintf("The given path [%s] is not valid.", $index), $code, $previous);
    }
}