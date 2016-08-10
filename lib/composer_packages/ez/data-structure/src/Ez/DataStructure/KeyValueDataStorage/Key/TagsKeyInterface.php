<?php

namespace Ez\DataStructure\KeyValueDataStorage\Key;

/**
 * Interface TagsKeyInterface
 *
 * @package Ez\DataStructure\KeyValueDataStorage\Key
 * @author Derek Li
 */
interface TagsKeyInterface
{
    /**
     * Convert the key (tags) to key.
     *
     * @return string|null
     */
    public function toKey();

    /**
     * Convert the key (string) to tags (array).
     *
     * @return array
     */
    public function toTags();

    /**
     * If the give tags existed in the key.
     *
     * @param array $tags
     * @return mixed
     */
    public function hasTags(array $tags);
}