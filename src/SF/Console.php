<?php

namespace SF;

/*
 * This file is part of the SimpleFrameworke
 *
 * (c) Jules Boussekeyt <jules.boussekeyt@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class Console
{
    private $kernel;
    private $commands;

    public function __construct(Kernel $kernel, $commands = array())
    {
        $this->kernel = $kernel;
        $this->commands = $commands;
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
