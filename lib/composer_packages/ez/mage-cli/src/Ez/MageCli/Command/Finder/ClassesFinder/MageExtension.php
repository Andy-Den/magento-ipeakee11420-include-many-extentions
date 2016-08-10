<?php

namespace Ez\MageCli\Command\Finder\ClassesFinder;

use Ez\MageBridge\MageBridge;

/**
 * Class MageExtension
 *
 * @package Ez\MageCli\Command\Finder\ClassesFinder
 * @author Derek Li
 */
class MageExtension extends FinderAbstract
{
    /**
     * @var MageBridge
     */
    protected $mageBridge = null;

    /**
     * @var array
     */
    protected $extensionNames = array();

    /**
     * Everything Magento should go through MageBridge.
     *
     * @param MageBridge $mageBridge
     * @return $this
     */
    public function setMageBridge(MageBridge $mageBridge)
    {
        $this->mageBridge = $mageBridge;
        return $this;
    }

    /**
     * Get MageBridge.
     *
     * @return MageBridge
     */
    public function getMageBridge()
    {
        return $this->mageBridge;
    }

    /**
     * Add the extension name.
     *
     * @param string $extensionName Extension name to add.
     * @return $this
     */
    public function addExtensionName($extensionName)
    {
        if (in_array($extensionName, $this->extensionNames)) {
            $this->extensionNames[] = $extensionName;
        }
        return $this;
    }

    /**
     * Load all the 3rd party extensionNames.
     *
     * @return array
     */
    protected function getAllExtensionNames()
    {
        $extensionNames = $this->getMageBridge()->_module_()->getAllExtensionNames();
        return is_array($extensionNames) ? $extensionNames : array();
    }

    /**
     * Set extension names.
     *
     * @param array $extensionNames
     * @return $this
     */
    public function setExtensionNames(array $extensionNames)
    {
        $this->extensionNames = $extensionNames;
        return $this;
    }

    /**
     * Get extension names to find.
     *
     * @return array
     */
    public function getExtensionNames()
    {
        if ($this->extensionNames == '*') {
            $this->extensionNames = $this->getAllExtensionNames();
        }
        return $this->extensionNames;
    }

    /**
     * Find all the command classes.
     *
     * @return $this
     */
    public function findCommandClasses()
    {
        foreach ($this->getExtensionNames() as $extensionName) {
            $extDir = $this
                ->getMageBridge()
                ->_module_()
                ->getExtensionDir($extensionName).DIRECTORY_SEPARATOR.'Command';
            $files = scandir($extDir);
            if (count($files) > 0) {
                foreach ($files as $f) {
                    if (fnmatch('*.php', $f)) {
                        $className = sprintf(
                            '%s_Command_%s',
                            $extensionName,
                            ucfirst(str_replace('.php', '', $f))
                        );
                        if (!in_array($className, $this->commandClasses)) {
                            $this->commandClasses[] = $className;
                        }
                    }
                }
            }
        }
        return $this;
    }
}
