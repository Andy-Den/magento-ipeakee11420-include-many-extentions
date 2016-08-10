<?php

namespace Ez\MageCli\Command\Finder\ClassesFinder;

/**
 * Class Dir
 *
 * @package Ez\MageCli\Command\Finder\ClassesFinder
 * @author Derek Li
 */
class Dir extends FinderAbstract
{
    protected $dirs = array();

    /**
     * Add a directory (with the class prefix).
     *
     * @param string $dir The directory to find.
     * @param string $classPrefix The class prefix used within the directory.
     * @return $this
     */
    public function addDir($dir, $classPrefix = 'Ez_MageCli_Command')
    {
        $this->dirs[$dir] = $classPrefix;
        return $this;
    }

    /**
     * Set directories.
     *
     * @param array $dirs
     * @return $this
     */
    public function setDirs(array $dirs)
    {
        $this->dirs = $dirs;
        return $this;
    }

    /**
     * Get all the directories.
     *
     * @return array
     */
    public function getDirs()
    {
        return $this->dirs;
    }

    /**
     * Scan all the directories to find the commands.
     *
     * @return $this
     */
    public function findCommandClasses()
    {
        $dirs = $this->getDirs();
        foreach ($dirs as $dir => $classPrefix) {
            $files = scandir($dir);
            if (count($files) > 0) {
                foreach ($files as $f) {
                    if (fnmatch('*.php', $f)) {
                        $this->findCommandClass($dir, $classPrefix, $f);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Find the command by default psr0 naming conventions.
     * e.g.
     * Reindex.php => Ez_MageCli_Command_Reindex
     *
     * @param string $dir The directory to look into.
     * @param string $classPrefix
     * @param string $file The name of the file.
     * @return $this
     */
    protected function findCommandClass($dir, $classPrefix, $file)
    {
        $filename = $dir.DIRECTORY_SEPARATOR.$file;
        $class = sprintf('%s_%s', $classPrefix, ucfirst(str_replace('.php', '', $file)));
        $this->commandClasses[$filename] = $class;
        return $this;
    }
}
