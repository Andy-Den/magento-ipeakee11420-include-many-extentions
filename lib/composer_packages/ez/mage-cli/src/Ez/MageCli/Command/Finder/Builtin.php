<?php

namespace Ez\MageCli\Command\Finder;

use Ez\MageCli\Command\Finder\ClassesFinder\Builtin as ClassesFinderBuiltin;
use Ez\MageCli\Command\BuiltinCommand;

class Builtin extends Dir
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

    /**
     * Get classes finder.
     *
     * @return ClassesFinderAbstract
     */
    public function getClassesFinder()
    {
        if (!isset($this->classesFinder)) {
            $this->classesFinder = new ClassesFinderBuiltin();
            $this->classesFinder->addDir(__DIR__.'/../Builtin', '\\Ez\\MageCli\\Command\Builtin');
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
                        $command = new $cc();
                        if ($command instanceof BuiltinCommand) {
                            $command->setFindersOptions($this->getFindersOptions());
                        }
                        $this->commands[] = $command;
                    }
                }
            }
        }
    }
}