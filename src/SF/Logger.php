<?php

namespace SF;

/*
 * This file is part of the SimpleFramework
 *
 * (c) Jules Boussekeyt <jules.boussekeyt@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class Logger
{
    const DEBUG     = 'debug';
    const WARNING   = 'warn';
    const ERROR     = 'error';
    const CRITICAL  = 'crit';

    private $logFile;

    public function __construct($logFile)
    {
        if (!file_exists($logFile)) {
            touch($logFile);
        }

        $this->logFile = $logFile;
    }

    public function log($message, $type = self::DEBUG)
    {
        $f = fopen($this->logFile, 'a');
        fwrite($f, static::format($message, $type));
        fclose($f);
    }

    protected static function format($message, $type)
    {
        return sprintf('[%s](%s) %s', date('Y/m/d H:i:s'), $type, $message);
    }
}
