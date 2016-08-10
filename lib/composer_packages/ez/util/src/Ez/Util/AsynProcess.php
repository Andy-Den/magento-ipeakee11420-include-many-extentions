<?php

namespace Ez\Util;

/**
 * Class AsynProcess
 * @package Ez\Util
 * @author Derek Li
 */
class AsynProcess
{
    /**
     * The command to push to the background.
     *
     * @var string
     */
    protected $cmd = null;

    /**
     * Constructor.
     *
     * @param null $cmd
     */
    public function __construct($cmd = null)
    {
        if (isset($cmd)) {
            $this->setCmd($cmd);
        }
    }

    /**
     * Set the command.
     *
     * @param $cmd
     * @return $this
     */
    public function setCmd($cmd)
    {
        $this->cmd = $cmd;
        return $this;
    }

    /**
     * Get the command.
     *
     * @return string
     */
    public function getCmd()
    {
        return $this->cmd;
    }

    /**
     * Execute the command in the background.
     *
     */
    public function exec()
    {
        if (substr(php_uname(), 0, 7) == "Windows") {
            pclose(popen("start /B " . $this->getCmd(), "r"));
        } else {
            exec($this->getCmd() . " > /dev/null &");
        }
    }
}