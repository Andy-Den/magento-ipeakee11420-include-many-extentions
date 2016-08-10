<?php

namespace Ez\MageCli\Command;

use Symfony\Component\Console\Command\Command;

/**
 * Extension class (for builtin commands) of the Symfony command class.
 *
 * @author Derek Li
 */
class BuiltinCommand extends Command
{
    /**
     * Configuration for the finders that a passed through to the console application.
     *
     * @var array
     */
    protected $findersOptions = array();

    /**
     * Set finders config.
     *
     * @param array $findersOptions
     * @return $this
     */
    public function setFindersOptions(array $findersOptions)
    {
        $this->findersOptions = $findersOptions;
        return $this;
    }

    /**
     * Get finders config.
     *
     * @return array
     */
    public function getFindersOptions()
    {
        return $this->findersOptions;
    }
}