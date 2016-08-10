<?php

namespace Ez\MageCli\Command\Finder;

/**
 * Interface FinderInterface
 *
 * @package Ez\MageCli\Command\ClassesFinder
 * @author Derek Li
 */
interface FinderInterface
{
    /**
     * @return array
     */
    public function getCommands();
}