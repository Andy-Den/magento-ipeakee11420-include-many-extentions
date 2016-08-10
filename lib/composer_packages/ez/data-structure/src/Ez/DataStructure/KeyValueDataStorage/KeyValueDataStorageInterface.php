<?php

namespace Ez\DataStructure\KeyValueDataStorage;

/**
 * Interface KeyValueDataStorageInterface
 *
 * @package Ez\DataStructure\KeyValueDataStorage
 * @author Derek Li
 */
interface KeyValueDataStorageInterface
{
    /**
     * Set the data for the key (could be tags).
     *
     * @param mixed $key The key for the data.
     * @param mixed $data The data.
     * @return $this
     */
    public function set($key, $data);

    /**
     * Get the data for the key.
     *
     * @param mixed $key The key to the value (could be tags).
     * @return mixed
     */
    public function get($key);

    /**
     * If the key exists in the container.
     *
     * @param mixed $key The key to the value (could be tags).
     * @return bool
     */
    public function has($key);

    /**
     * Find the data for the given search terms.
     *
     * @param mixed $search Could be key word(s), could be tags, ...
     * @return mixed
     */
    public function find($search);

    /**
     * Copy the data from '$fromKey' and assign it to '$toKey'.
     * This can give the same data multiple access points.
     *
     * @param string|array $fromKey Key or tags that data is from.
     * @param string|array $toKey Another key that data will be reflected to.
     * @param bool $force OPTIONAL If force to assign the data to '$toKey' when there is data for it.
     * @return $this
     */
    public function duplicate($fromKey, $toKey, $force = false);
}