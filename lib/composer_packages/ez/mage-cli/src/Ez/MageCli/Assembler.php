<?php

namespace Ez\MageCli;

use Symfony\Component\Console\Application as SymfonyConsole;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

/**
 * Class Assembler
 *
 * @package Ez\MageCli
 * @author Derek Li
 */
class Assembler
{
    /**
     * Directories to scan.
     *
     * @var array
     */
    protected $dirs = array();

    /**
     * The Symfony console.
     *
     * @var SymfonyConsole
     */
    protected $console = null;

    /**
     * All the commands to assemble.
     *
     * @var array
     */
    protected $commands = array();

    /**
     * Constructor.
     * Receive the Symfony console application.
     *
     * @param SymfonyConsole $console
     */
    public function __construct(SymfonyConsole $console)
    {
        $this->console = $console;
    }

    /**
     * Get the Symfony console.
     *
     * @return SymfonyConsole
     */
    public function getConsole()
    {
        return $this->console;
    }

    /**
     * Grab all the commands and add them into console.
     *
     * @return $this
     */
    public function assemble()
    {
        foreach ($this->getCommands() as $command) {
            if ($command instanceof SymfonyCommand) {
                $this->getConsole()->add($command);
            }
        }
        return $this;
    }

    /**
     * Add all the commands.
     *
     * @return array
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * Add a single command.
     *
     * @param SymfonyCommand $command
     * @return $this
     */
    public function addCommand(SymfonyCommand $command)
    {
        $command->setApplication($this->getConsole());
        $this->commands[] = $command;
        return $this;
    }

    /**
     * Add commands.
     *
     * @param array $commands Commands to add.
     * @return $this
     */
    public function addCommands(array $commands)
    {
        foreach ($commands as $c) {
            $this->addCommand($c);
        }
        return $this;
    }
}
