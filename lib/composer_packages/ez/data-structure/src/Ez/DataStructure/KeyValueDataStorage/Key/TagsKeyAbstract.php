<?php

namespace Ez\DataStructure\KeyValueDataStorage\Key;

/**
 * Class TagsKeyAbstract
 *
 * @package Ez\DataStructure\KeyValueDataStorage\Key
 * @author Derek Li
 */
abstract class TagsKeyAbstract implements TagsKeyInterface, KeyInterface
{
    /**
     * Tags.
     *
     * @var array
     */
    protected $tags = null;

    /**
     * Key.
     *
     * @var string
     */
    protected $key = null;

    /**
     * Constructor.
     * Pass through the key or tags.
     *
     * @param array|string $keyOrTags
     */
    public function __construct($keyOrTags)
    {
        if (is_array($keyOrTags)) {
            $this->tags = $keyOrTags;
        } else {
            $this->key = $keyOrTags;
        }
    }

    /**
     * Get the key.
     *
     * @return string|null
     */
    public function toKey()
    {
        if (!isset($this->key)) {
            if (!is_array($this->tags) ||
                count($this->tags) === 0
            ) {
                $this->key = null;
            } else {
                sort($this->tags);
                $this->key = $this->tagsToKey($this->tags);
            }
        }
        return $this->key;
    }

    /**
     * Get the tags.
     *
     * @return array
     */
    public function toTags()
    {
        if (!isset($this->tags)) {
            if (!isset($this->key)) {
                $this->tags = array();
            } else {
                $this->tags = $this->keyToTags($this->key);
            }
        }
        return $this->tags;
    }

    /**
     * Check if the tags are part of the key.
     *
     * @param array $tags
     * @return bool|mixed
     */
    public function hasTags(array $tags)
    {
        return count(array_intersect($tags, $this->toTags())) === count($tags);
    }

    /**
     * Get the key.
     *
     * @return null|string
     */
    public function getKey()
    {
        return $this->toKey();
    }

    /**
     * Convert the tags to key.
     *
     * @param array $tags
     * @return string
     */
    abstract protected function tagsToKey(array $tags);

    /**
     * Convert the key to tags.
     *
     * @param string $key
     * @return array
     */
    abstract protected function keyToTags($key);
}
