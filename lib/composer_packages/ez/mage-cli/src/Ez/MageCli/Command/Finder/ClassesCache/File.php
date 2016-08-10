<?php

namespace Ez\MageCli\Command\Finder\ClassesCache;

class File
{
    /**
     * @var string
     */
    protected $filename = null;

    /**
     * The filename of the cache file.
     *
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Check if the cache exits.
     *
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->filename);
    }

    /**
     * Get the command classes cached.
     *
     * @return array
     */
    public function getCommandClasses()
    {
        return $this->commandClasses;
    }

    /**
     * Write the cache.
     *
     * @param array $commandClasses
     * @return $this
     */
    public function write(array $commandClasses)
    {
        if ($this->exists()) {
            unlink($this->filename);
        }
        file_put_contents($this->filename, serialize($commandClasses));
        return $this;
    }

    /**
     * Read the cache.
     *
     * @return false|array Return false if the cache doesn't exist or invalid.
     */
    public function read()
    {
        if (!$this->exists()) {
            return false;
        }
        $content = file_get_contents($this->filename);
        if (empty($content)) {
            return false;
        }
        $commandClasses = unserialize($content);
        if (!is_array($commandClasses)) {
            unlink($this->filename);
            return false;
        }
        return $commandClasses;
    }
}