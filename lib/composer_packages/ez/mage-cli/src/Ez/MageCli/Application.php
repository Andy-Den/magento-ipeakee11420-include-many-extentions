<?php

namespace Ez\MageCli;

use Symfony\Component\Console\Application as SymfonyConsole;
use Ez\MageBridge\MageBridge;
use Ez\MageCli\Command\Finder\FinderAbstract;
use Ez\MageCli\Command\Finder\FinderInterface;
use Ez\MageCli\Command\Finder\Builtin as BuiltinFinder;

/**
 * Class Application
 *
 * @todo To find a better way to cache as much as possible.
 *      The current problem is when caching all the commands,
 *      errors might occur due to the fail of serializing closures.
 * @package Ez\MageCli
 * @author Derek Li
 */
class Application
{
    /**
     * @var
     */
    protected $symfonyConsole = null;

    /**
     * The configurations for the finders.
     *
     * @var array
     */
    protected $findersOptions = array();

    /**
     * @var null
     */
    protected $assembler = null;

    /**
     * Constructor.
     *
     * @param SymfonyConsole $symfonyConsole The Symfony console to use.
     * @param array $findersOptions OPTIONAL The places to look for commands.
     */
    public function __construct(SymfonyConsole $symfonyConsole, array $findersOptions = null)
    {
        $this->symfonyConsole = $symfonyConsole;
        if (isset($findersOptions)) {
            $this->setFindersOptions($findersOptions);
        }
    }

    /**
     * Get the Symfony console.
     *
     * @return SymfonyConsole
     */
    public function getSymfonyConsole()
    {
        return $this->symfonyConsole;
    }

    /**
     * Set the configurations for the finders.
     *
     * @param array $findersOptions The places to look for commands.
     * @return $this
     */
    public function setFindersOptions(array $findersOptions)
    {
        $this->findersOptions = $findersOptions;
        return $this;
    }

    /**
     * Get the configurations for the finders.
     *
     * @return array
     */
    public function getFindersOptions()
    {
        return $this->findersOptions;
    }

    /**
     * Get the assembler.
     *
     * @return Assembler
     */
    public function getAssembler()
    {
        if (!isset($this->assembler)) {
            $this->assembler = new Assembler($this->getSymfonyConsole());
        }
        return $this->assembler;
    }

    /**
     * Load the finder.
     *
     * @param string $finderType Name of the finder.
     * @param array $finderOptions Options for the finder.
     * @return FinderInterface
     * @throws \Exception
     */
    protected function loadFinder($finderType, array $finderOptions = null)
    {
        // Get the classes.
        $finderClass = sprintf(
            "\\Ez\\MageCli\\Command\\Finder\\%s",
            ucfirst($finderType)
        );
        if (!class_exists($finderClass, true)) {
            throw new \Exception(sprintf('The finder class [%s] is not found', $finderClass));
        }
        if (isset($finderOptions)) {
            return new $finderClass($finderOptions);
        } else {
            return new $finderClass();
        }
    }

    /**
     * Find all the commands based on the given options.
     *
     * @return array
     */
    public function findCommands()
    {
        $commands = array();
        foreach ($this->getFindersOptions() as $o) {
            if (array_key_exists('type', $o)) {
                if (is_array($o['options'])) {
                    $finder = $this->loadFinder($o['type'], $o['options']);
                } else {
                    $finder = $this->loadFinder($o['type']);
                }
                $commands = array_merge($commands, $finder->getCommands());
            }
        }
        $builtinFinder = new BuiltinFinder();
        $builtinFinder->setFindersOptions($this->getFindersOptions());
        $commands = array_merge($commands, $builtinFinder->getCommands());
        return $commands;
    }

    /**
     * Start the console.
     *
     * @return $this
     */
    public function run()
    {
        $assembler = $this->getAssembler()
            ->addCommands($this->findCommands())
            ->assemble();
        $assembler->getConsole()->run();
    }
}
