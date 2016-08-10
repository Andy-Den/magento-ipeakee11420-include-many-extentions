<?php

namespace Ez\MageCli\Command\Finder\ClassesFinder;

/**
 * Interface FinderInterface
 *
 * @package Ez\MageCli\Command\Finder\ClassesFinder
 * @author Derek Li
 */
interface FinderInterface
{
    /**
     * Get the command classes.
     *
     * @return array
     */
    public function getCommandClasses();
}