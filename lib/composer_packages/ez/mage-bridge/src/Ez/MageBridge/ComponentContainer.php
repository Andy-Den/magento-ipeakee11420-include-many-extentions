<?php

namespace Ez\MageBridge;

use Ez\MageBridge\Exception\ComponentNotExistException;
use Ez\MageBridge\Component\ComponentInterface;
use Ez\DataStructure\KeyValueDataStorage\JsonTagsValueDataStorage;

/**
 * Class ComponentContainer
 *
 * @package Ez\MageBridge
 * @author Derek Li
 */
class ComponentContainer
{
    /**
     * Components.
     *
     * @var array
     */
    protected $components = array();

    /**
     * @var Config
     */
    protected $config = null;

    /**
     * @var JsonTagsValueDataStorage
     */
    protected $dataStorage = null;

    /**
     * Constructor.
     *
     * @param Config $config
     * @param JsonTagsValueDataStorage $dataStorage
     */
    public function __construct(Config $config, JsonTagsValueDataStorage $dataStorage)
    {
        $this->config = $config;
        $this->dataStorage = $dataStorage;
    }

    /**
     * Set the component.
     *
     * @param string $componentName Name of the component.
     * @param mixed $component The component to set.
     * @return $this
     */
    public function set($componentName, $component)
    {
        $this->components[$componentName] = $component;
        return $this;
    }

    /**
     * Get the component (in a lazy load way).
     *
     * @param string $componentName Name of the component.
     * @return mixed
     */
    public function get($componentName)
    {
        if (!array_key_exists($componentName, $this->components)) {
            $this->loadComponent($componentName);
        }
        return $this->components[$componentName];
    }

    /**
     * Load the component.
     *
     * @param string $componentName Name of the component.
     * @return $this
     * @throws ComponentNotExistException
     */
    protected function loadComponent($componentName)
    {
        $componentClass = '\\Ez\\MageBridge\\Component\\'.ucfirst($componentName);
        if (!class_exists($componentClass, true)) {
            throw new ComponentNotExistException($componentName);
        }
        $component = new $componentClass();
        if ($component instanceof ComponentInterface) {
            $component
                ->setConfig($this->config)
                ->setDataStorage($this->dataStorage);
        }
        $this->set($componentName, $component);
        return $this;
    }
}
