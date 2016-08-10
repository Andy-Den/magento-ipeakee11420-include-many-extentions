<?php

namespace Maintenance;

/**
 * Class Handler
 * 
 * @author Derek Li
 */
class Handler
{
    /**
     * Ips that would be allowed to come through during maintenance.
     *
     * @var array
     */
    protected $whiteListIps = array();

    /**
     * The stores to include in the maintenance.
     *
     * @var array
     */
    protected $includedStores = array();

    /**
     * If all stores are included in the maintenance.
     *
     * @var bool
     */
    protected $includeAllStores = true;

    /**
     * Current ip address to check.
     *
     * @var string
     */
    protected $ip = null;

    /**
     * Current store to check.
     *
     * @var string
     */
    protected $store = null;

    /**
     * Handler constructor.
     *
     * @param array|null $options
     */
    public function __construct(array $options = null)
    {
        if (isset($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Set options.
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        foreach ($options as $opt => $val) {
            $setMethod = sprintf(
                'set%s',
                str_replace(' ', '', ucwords(str_replace('_', ' ', $opt)))
            );
            if (method_exists($this, $setMethod)) {
                $this->{$setMethod}($val);
            }
        }
        return $this;
    }

    /**
     * Set whilte list ip addresses.
     *
     * @param array $ips The ips to white list.
     * @return $this
     */
    public function setWhiteListIps(array $ips)
    {
        $this->whiteListIps = $ips;
        return $this;
    }

    /**
     * Get the white listed ips.
     *
     * @return array
     */
    public function getWhiteListIps()
    {
        return $this->whiteListIps;
    }

    /**
     * Set the stores included in the maintenance.
     *
     * @param array $stores The stores to include.
     * @return $this
     */
    public function setIncludedStores(array $stores)
    {
        $this->includedStores = $stores;
        return $this;
    }

    /**
     * Get included stores.
     *
     * @return array
     */
    public function getIncludedStores()
    {
        return $this->includedStores;
    }

    /**
     * @param bool $trueOrFalse True if including all stores or false otherwise.
     * @return $this
     */
    public function setIncludeAllStores($trueOrFalse)
    {
        $this->includeAllStores = $trueOrFalse;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIncludeAllStores()
    {
        return $this->includeAllStores;
    }

    /**
     * @param string $ip The ip to check.
     * @return $this
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->store = $store;
        return $this;
    }

    /**
     * @return string
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * If the ip is white listed.
     *
     * @return bool
     */
    public function isIpWhiteListed()
    {
        $ip = $this->getIp();
        // Empty ip is always considered as not white listed.
        if (empty($ip)) {
            return false;
        }
        $whiteListIps = $this->getWhiteListIps();
        if (count($whiteListIps) === 0) {
            return false;
        }
        foreach ($whiteListIps as $whiteIp) {
            /**
             * The given ip can be a combined one like "xxx.xxx.xxx.xxx, xxx.xxx.xxx.xxx".
             * So can't check if it exists in the white list ips array.
             */
            if (strpos($ip, $whiteIp) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the store is included in the maintenance.
     *
     * @param string $store The store to check.
     * @return bool
     */
    public function isStoreIncluded()
    {
        $store = $this->getStore();
        // Empty store (usually default store) is always considered as included.
        if (empty($store)) {
            return true;
        }
        return in_array($store, $this->includedStores);
    }

    /**
     * Check if the maintenance should be on.
     *
     * @return bool
     */
    public function isOn()
    {
        // White listed ip always gets a pass.
        if ($this->isIpWhiteListed()) {
            return false;
        }
        // If not including all stores and the current store is exlucded, it will be a pass.
        if (!$this->getIncludeAllStores() &&
            !$this->isStoreIncluded()
        ) {
            return false;
        }
        return true;
    }
}