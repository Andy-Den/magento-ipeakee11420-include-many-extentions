<?php

namespace Ez\MageBridge\Component;

/**
 * Class Module
 *
 * @package Ez\MageBridge\Component
 * @author Derek Li
 */
class Module extends ComponentAbstract
{
    /**
     * @var array
     */
    protected $moduleConfigArray = null;

    /**
     * All 3rd party extension names.
     *
     * @var null
     */
    protected $extensionNames = null;

    /**
     * Get Magento module configuration as array.
     *
     * @return array
     */
    public function getModuleConfigArray()
    {
        $tags = array('module_config_array');
        if (!$this->getDataStorage()->has($tags)) {
            $this->getDataStorage()->set($tags, \Mage::getConfig()->getModuleConfig()->asArray());
        }
        return $this->getDataStorage()->get($tags);
    }

    /**
     * Get all 3rd party extension names.
     *
     * @return array|null
     */
    public function getAllExtensionNames()
    {
        $tags = array('all_extension_names');
        if (!$this->getDataStorage()->has($tags)) {
            $extensionNames = array();
            foreach ($this->getModuleConfigArray() as $module => $c) {
                if ($c['codePool'] !== 'core') {
                    $extensionNames[] = $module;
                }
            }
            $this->getDataStorage()->set($tags, $extensionNames);
        }
        return $this->getDataStorage()->get($tags);
    }

    /**
     * Get extension directory.
     *
     * @param $extensionName
     * @return string
     */
    public function getExtensionDir($extensionName)
    {
        $tags = array('extension_dir', $extensionName);
        if (!$this->getDataStorage()->has($tags)) {
            $this->getDataStorage()->set($tags, rtrim(\Mage::getModuleDir(null, $extensionName), DIRECTORY_SEPARATOR));
        }
        return $this->getDataStorage()->get($tags);
    }
}