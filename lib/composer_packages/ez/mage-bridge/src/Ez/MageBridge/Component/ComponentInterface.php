<?php

namespace Ez\MageBridge\Component;

use Ez\DataStructure\KeyValueDataStorage\JsonTagsValueDataStorage;
use Ez\MageBridge\Config;

/**
 * Interface ComponentInterface
 * @package Ez\MageBridge\Component
 * @author Derek Li
 */
interface ComponentInterface
{
    /**
     * @param Config $config
     * @return $this
     */
    public function setConfig(Config $config);

    /**
     * @return Config
     */
    public function getConfig();

    /**
     * @param JsonTagsValueDataStorage $dataStorage
     * @return $this
     */
    public function setDataStorage(JsonTagsValueDataStorage $dataStorage);

    /**
     * @return JsonTagsValueDataStorage
     */
    public function getDataStorage();
}