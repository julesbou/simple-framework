<?php

namespace SimpleFramework;

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
