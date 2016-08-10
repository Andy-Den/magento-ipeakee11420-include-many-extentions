<?php

namespace Ez\MageBridge;

use Ez\MageBridge\Exception\ResourceNotExistException;
use Ez\MageBridge\Component\ComponentInterface;
use Ez\DataStructure\KeyValueDataStorage\JsonTagsValueDataStorage;

/**
 * Class MageBridge
 *
 * @package Ez\MageBridge
 * @author Derek Li
 */
class MageBridge
{
    /**
     * @var Config
     */
    protected $config = null;

    /**
     * @var JsonTagsValueDataStorage
     */
    protected $dataStorage = null;

    /**
     * @var ComponentContainer
     */
    protected $componentContainer = null;

    /**
     * @param Config $config
     */
    public function __construct(Config $config = null)
    {
        if (isset($config)) {
            $this->config = $config;
        }
    }

    /**
     * Set the configuration.
     *
     * @param Config $config
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Get the configuration.
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get data storage.
     *
     * @return JsonTagsValueDataStorage
     */
    public function getDataStorage()
    {
        if (!isset($this->dataStorage)) {
            $this->dataStorage = new JsonTagsValueDataStorage();
        }
        return $this->dataStorage;
    }

    /**
     * Get component container.
     *
     * @return ComponentContainer
     */
    public function getComponentContainer()
    {
        if (!isset($this->componentContainer)) {
            $this->componentContainer = new ComponentContainer($this->getConfig(), $this->getDataStorage());
        }
        return $this->componentContainer;
    }

    /**
     * Include Magento.
     *
     * @return $this
     */
    public function includeMage()
    {
        // Only initialize the Magento application once when MageBridge is instantiated.
        require_once $this->getConfig()->get('general/mage_root_dir').DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Mage.php';
        \Mage::app($this->getConfig()->get('general/mage_run_code'), $this->getConfig()->get('general/mage_run_type'));
        return $this;
    }

    /**
     * Get the component.
     *
     * @param string $componentName Name of the component.
     * @return ComponentInterface
     */
    protected function getComponent($componentName)
    {
        return $this->getComponentContainer()->get($componentName);
    }

    /**
     * Magically get component (format: _componentName_()).
     *
     * @param string $method The name of the method called.
     * @param array $args Arguments passed to the method.
     * @return ComponentInterface|null
     */
    public function __call($method, $args)
    {
        if (preg_match('/^_([a-zA-Z1-9]+)_$/', $method, $matches)) {
            return $this->getComponent($matches[1]);
        }
    }
}