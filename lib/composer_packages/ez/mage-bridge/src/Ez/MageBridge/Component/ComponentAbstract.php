<?php

namespace Ez\MageBridge\Component;

use Ez\DataStructure\KeyValueDataStorage\JsonTagsValueDataStorage;
use Ez\MageBridge\Config;

/**
 * Class ComponentAbstract
 *
 * @package Ez\MageBridge\Component
 * @author Derek Li
 */
class ComponentAbstract implements ComponentInterface
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
     * @param Config $config
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param JsonTagsValueDataStorage $dataStorage
     * @return $this
     */
    public function setDataStorage(JsonTagsValueDataStorage $dataStorage)
    {
        $this->dataStorage = $dataStorage;
        return $this;
    }

    /**
     * @return JsonTagsValueDataStorage
     */
    public function getDataStorage()
    {
        return $this->dataStorage;
    }
}