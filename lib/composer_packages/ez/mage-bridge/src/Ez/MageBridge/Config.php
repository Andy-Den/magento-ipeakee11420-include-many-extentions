<?php

namespace Ez\MageBridge;

use Ez\DataStructure\IndexedDeepMergeArray;

/**
 * Class Config
 *
 * @package Ez\MageBridge
 * @author Derek Li
 */
class Config extends IndexedDeepMergeArray
{
    /**
     * Default configuration.
     *
     * @var array
     */
    protected $array = array(
        'general' => array(
            'mage_root_dir' => '',
            'mage_run_code' => 'admin',
            'mage_run_type' => 'store'
        )
    );

    /**
     * Update the configuration.
     *
     * @param array $config
     * @return $this
     */
    public function updateConfig(array $config)
    {
        $this->addArray($config);
        return $this;
    }
}
