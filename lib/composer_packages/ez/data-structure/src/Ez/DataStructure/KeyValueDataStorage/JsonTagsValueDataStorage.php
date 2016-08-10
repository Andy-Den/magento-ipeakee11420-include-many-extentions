<?php

namespace Ez\DataStructure\KeyValueDataStorage;

use Ez\DataStructure\KeyValueDataStorage\Key\JsonTags as JsonTagsKey;

/**
 * Class JsonTags
 *
 * @package Ez\DataStructure\KeyValueDataStorage\Key
 * @author Derek Li
 */
class JsonTagsValueDataStorage implements KeyValueDataStorageInterface
{
    /**
     * @var array
     */
    protected $data = array();

    /**
     * Constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = null)
    {
        if (isset($data)) {
            $this->data = $data;
        }
    }

    /**
     * Set the data for the key (could be tags).
     *
     * @param mixed $key The key for the data (can be string, array or instance of JsonTagsKey)
     * @param mixed $data The data.
     * @return $this
     */
    public function set($key, $data)
    {
        if (!isset($key)) {
            return $this;
        }
        $this->data[$this->serializeKey($key)] = $data;
        return $this;
    }

    /**
     * Get the data for the key.
     *
     * @param mixed $key OPTIONAL The key to the value (could be tags).
     * @return mixed
     */
    public function get($key = null)
    {
        if (!isset($key)) {
            return $this->data;
        }
        $key = $this->serializeKey($key);
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }
    }

    /**
     * Serialize the key into string and return it.
     *
     * @param $key
     * @return string|null
     */
    protected function serializeKey($key)
    {
        if (!$key instanceof JsonTagsKey) {
            $key = new JsonTagsKey(is_array($key) ? $key : array($key));
        }
        return $key->getKey();
    }

    /**
     * If the key exists in the container.
     *
     * @param mixed $key The key to the value (could be tags).
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($this->serializeKey($key), $this->data);
    }

    /**
     * Find the data for the given search terms.
     *
     * @param mixed $search Could be key word(s), could be tags, ...
     * @return mixed
     */
    public function find($search)
    {
        $search = !is_array($search) ? array($search) : $search;
        return $this->findByTags($search);
    }

    /**
     * Find all the values if their keys contain the given tags.
     *
     * @param array $tags
     * @return array
     */
    public function findByTags(array $tags)
    {
        $result = array();
        foreach ($this->data as $key => $value) {
            $jsonTags = new JsonTagsKey($key);
            if ($jsonTags->hasTags($tags)) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * Copy the data from '$fromKey' and assign it to '$toKey'.
     * This can give the same data multiple access points.
     *
     * @param string|array $fromKey Key or tags that data is from.
     * @param string|array $toKey Another key that data will be reflected to.
     * @param bool $force OPTIONAL If force to assign the data to '$toKey' when there is data for it.
     * @return $this
     */
    public function duplicate($fromKey, $toKey, $force = false)
    {
        $this->set($toKey, $this->get($fromKey));
        return $this;
    }
}
