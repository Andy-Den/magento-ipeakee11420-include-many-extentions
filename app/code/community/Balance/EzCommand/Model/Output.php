<?php

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Balance_EzCommand_Model_Output
 *
 * @author Derek Li
 */
class Balance_EzCommand_Model_Output
{
    /**
     * @var OutputInterface
     */
    protected $output = null;

    /**
     * Balance_EzCommand_Model_Output constructor.
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param mixed $msg The message to print out as standard output.
     * @return $this
     */
    public function error($msg)
    {
        return $this->message(sprintf("<error>%s</error>", $msg));
    }

    /**
     * @param mixed $msg The message to print out as standard output.
     * @return $this
     */
    public function info($msg)
    {
        return $this->message(sprintf("<info>%s</info>", $msg));
    }

    /**
     * @param mixed $msg The message to print out as standard output.
     * @param bool $ln If line escape.
     * @return $this
     */
    public function message($msg, $ln = true)
    {
        if (is_array($msg)) {
            $msg = Zend_Json::encode($msg);
        }
        if ($ln) {
            $this->getOutput()->writeln($msg);
        } else {
            $this->getOutput()->write($msg);
        }
        return $this;
    }
}