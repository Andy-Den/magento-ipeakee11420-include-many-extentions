<?php

namespace Ez\DataStructure\KeyValueDataStorage\Key;

/**
 * Class JsonTags
 *
 * @package Ez\DataStructure\KeyValueDataStorage\Key
 * @author Derek Li
 */
class JsonTags extends TagsKeyAbstract
{
    /**
     * Convert the tags to key.
     *
     * @param array $tags
     * @return string
     */
    protected function tagsToKey(array $tags)
    {
        return json_encode($tags);
    }

    /**
     * Convert the key to tags.
     *
     * @param string $key
     * @return array
     */
    protected function keyToTags($key)
    {
        return json_decode($key, true);
    }
}
