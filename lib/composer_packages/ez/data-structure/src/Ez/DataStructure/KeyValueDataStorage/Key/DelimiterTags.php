<?php

namespace Ez\DataStructure\KeyValueDataStorage\Key;

/**
 * Class DelimiterTags
 *
 * @package Ez\DataStructure\KeyValueDataStorage\Key
 * @author Derek Li
 */
class DelimiterTags extends TagsKeyAbstract
{
    /**
     * @var string
     */
    protected $delimiter = '{t}';

    /**
     * Convert the tags to key.
     *
     * @param array $tags
     * @return string
     */
    protected function tagsToKey(array $tags)
    {
        return implode($this->delimiter, $tags);
    }

    /**
     * Convert the key to tags.
     *
     * @param string $key
     * @return array
     */
    protected function keyToTags($key)
    {
        return explode($this->delimiter, $key);
    }
}
