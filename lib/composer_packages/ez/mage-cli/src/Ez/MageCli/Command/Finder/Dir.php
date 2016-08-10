<?php

namespace Ez\MageCli\Command\Finder;

use Ez\MageCli\Command\Finder\ClassesFinder\Dir as ClassesFinderDir;

class Dir extends FinderAbstract
{
    /**
     * Get classes finder.
     *
     * @return ClassesFinderAbstract
     */
    public function getClassesFinder()
    {
        if (!isset($this->classesFinder)) {
            $this->classesFinder = new ClassesFinderDir($this->getClassesFinderOptions());
        }
        return $this->classesFinder;
    }

    /**
     * @return $this
     */
    public function findCommands()
    {
        $commandClasses = $this->findCommandClasses();
        if (count($commandClasses) > 0) {
            foreach ($commandClasses as $filename => $cc) {
                if (file_exists($filename)) {
                    require_once $filename;
                    if (class_exists($cc, true)) {
                        $this->commands[] = new $cc();
                    }
                }
            }
        }
    }
}