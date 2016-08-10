<?php

namespace Ez\MageCli\Command\Finder;

use Ez\MageCli\Command\Finder\ClassesFinder\MageExtension as ClassesFinderMageExtension;

class MageExtension extends FinderAbstract
{
    /**
     * @return ClassesFinderMageExtension
     */
    public function getClassesFinder()
    {
        if (!isset($this->classesFinder)) {
            $this->classesFinder = new ClassesFinderMageExtension($this->getClassesFinderOptions());
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
            foreach ($commandClasses as $cc) {
                if (class_exists($cc, true)) {
                    $this->commands[] = new $cc();
                }
            }
        }
    }
}