<?php

namespace Ez\MageCli\Command\Finder;

use Ez\MageCli\Command\Finder\ClassesFinder\FinderAbstract as ClassesFinderAbstract;
use Ez\MageCli\Command\Finder\ClassesCache\File as ClassesCacheFile;

abstract class FinderAbstract implements FinderInterface
{
    /**
     * @var array
     */
    protected $commands = array();

    /**
     * @var array
     */
    protected $classesFinderOptions = array();

    /**
     * @var ClassesFinderAbstract
     */
    protected $classesFinder = null;

    /**
     * @var null
     */
    protected $classesCacheFilename = null;

    /**
     * @var ClassesCacheFile
     */
    protected $classesCache = null;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = null)
    {
        if (isset($options)) {
            $this->mapOptions($options);
        }
    }

    /**
     * Map options.
     *
     * @param array $options
     * @return $this
     */
    public function mapOptions(array $options)
    {
        foreach ($options as $name => $value) {
            $localSetMethod = sprintf('set%s', str_replace(' ', '', ucwords(str_replace('_', ' ', $name))));
            if (method_exists($this, $localSetMethod)) {
                $this->{$localSetMethod}($value);
            }
        }
        return $this;
    }

    /**
    /**
     * Get the commands.
     *
     * @return array|null
     */
    public function getCommands()
    {
        if (count($this->commands) === 0) {
            $this->findCommands();
        }
        return $this->commands;
    }

    /**
     * @param array $options The options set to the classes finder.
     * @return $this
     */
    public function setClassesFinderOptions(array $options)
    {
        $this->classesFinderOptions = $options;
        return $this;
    }

    /**
     * @return array
     */
    public function getClassesFinderOptions()
    {
        return $this->classesFinderOptions;
    }

    /**
     * Set the filename of the classes cache used.
     *
     * @param $filename
     * @return $this
     */
    public function setClassesCacheFilename($filename)
    {
        $this->classesCacheFilename = $filename;
        return $this;
    }

    /**
     * Get the filename of the classes cache used.
     *
     * @return string
     */
    public function getClassesCacheFilename()
    {
        return $this->classesCacheFilename;
    }

    /**
     * Get the classes cache.
     *
     * @return ClassesCacheFile
     */
    public function getClassesCache()
    {
        if (!isset($this->classesCache)) {
            $this->classesCache = new ClassesCacheFile($this->getClassesCacheFilename());
        }
        return $this->classesCache;
    }

    /**
     * Find the command classes.
     *
     * @return array
     */
    public function findCommandClasses()
    {
        // Get the command classes.
        $classesCache = $this->getClassesCache();
        $classesCached = $classesCache->read();
        // Use cache.
        if (is_array($classesCached)) {
            return $classesCached;
        } else { // Find all the command classes and set the cache.
            $commandClasses = $this->getClassesFinder()->getCommandClasses();
            $classesCache->write($commandClasses);
            return $commandClasses;
        }
    }

    /**
     * Find all the commands.
     *
     * @return $this
     */
    public abstract function findCommands();
}