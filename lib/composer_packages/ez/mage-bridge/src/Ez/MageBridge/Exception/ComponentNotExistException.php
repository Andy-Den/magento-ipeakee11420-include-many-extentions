<?php

namespace Ez\MageBridge\Exception;

/**
 * Class ComponentNotExistException
 *
 * @package Ez\Util\Exception
 * @author Derek Li
 */
class ComponentNotExistException extends \Exception
{
    /**
     * The exception code.
     *
     * @var string
     */
    protected $code = '710001';

    /**
     * Override the message passed through.
     *
     * @param string $resourceName Name of the resource.
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($resourceName, $code = 0, \Exception $previous = null)
    {
        parent::__construct(sprintf("The requested component [%s] does not exist.", $resourceName), $code, $previous);
    }
}