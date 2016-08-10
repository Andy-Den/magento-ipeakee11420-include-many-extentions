<?php

namespace Ez\MageCli\Command\Finder\ClassesFinder;

/**
 * Class Dir
 *
 * @package Ez\MageCli\Command\Finder\ClassesFinder
 * @author Derek Li
 */
class Builtin extends Dir
{
    /**
     * Find the command by php namespace naming conventions.
     *
     * @param string $dir The directory to look into.
     * @param string $classPrefix
     * @param string $file The name of the file.
     * @return $this
     */
    protected function findCommandClass($dir, $classPrefix, $file)
    {
        $filename = $dir.DIRECTORY_SEPARATOR.$file;
        $class = sprintf('%s\\%s', $classPrefix, ucfirst(str_replace('.php', '', $file)));
        $this->commandClasses[$filename] = $class;
        return $this;
    }
}
