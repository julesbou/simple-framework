<?php

namespace SF;

class Console
{
    private $kernel;
    private $commands = array();

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    public function register($name, $command)
    {
        $this->commands[$name] = $command;
    }

    public function run($argv)
    {
        if (!isset($argv[1])) {
            echo "please specify a command:\n";
            echo '- '.implode("\n- ", array_keys($this->commands));
            exit;
        }

        $name = $argv[1];

        if (!isset($this->commands[$name])) {
            echo "invalid command '{$name}'\n";
            exit;
        }

        $command = $this->commands[$name];
        $args = array_splice($argv, 0, 2);

        $pair = true;
        $prev;
        foreach ($argv as $k => $val) {
            if (!$pair) {
            $argv[$prev] = $val;
            } else {
                $prev = $val;
            }
            $pair = !$pair;
            unset($argv[$k]);
        }

        if (!method_exists($command, 'run')) {
            echo "command '{$name}' should have a run() method\n";
            exit;
        }

        $command->run($this->kernel->getContainer(), $argv);
    }
}
