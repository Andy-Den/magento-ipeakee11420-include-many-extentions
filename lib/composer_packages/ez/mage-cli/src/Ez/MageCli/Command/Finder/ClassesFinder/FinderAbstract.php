<?php

namespace Ez\MageCli\Command\Finder\ClassesFinder;

abstract class FinderAbstract implements FinderInterface
{
    /**
     * Command classes to use.
     *
     * @var array
     */
    protected $commandClasses = null;

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
     * Get all the command classes.
     *
     * @return array
     */
    public function getCommandClasses()
    {
        if (!isset($this->commandClasses)) {
            $this->findCommandClasses();
        }
        return $this->commandClasses;
    }

    /**
     * @return $this
     */
    abstract function findCommandClasses();
}