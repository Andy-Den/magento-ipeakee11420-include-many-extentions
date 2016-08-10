<?php

namespace Ez\DataStructure\Exception;

/**
 * Class IndexNotExistException
 *
 * @package Ez\DataStructure\Exception
 * @author Derek Li
 */
class IndexNotExistException extends \Exception
{
    /**
     * The exception code.
     *
     * @var string
     */
    protected $code = '700003';

    /**
     * Override the message passed through.
     *
     * @param string $index Name of the attribute.
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($index, $code = 0, \Exception $previous = null)
    {
        parent::__construct(sprintf("The given path [%s] does not exist.", $index), $code, $previous);
    }
}