<?php

namespace SF\Tests;

use SF\Logger;

class MockLogger extends Logger
{
    public static function format($message, $type)
    {
        return sprintf('%s_|_%s', $message, $type);
    }
}

class LoggerTest extends \PHPUnit_Framework_TestCase
{
    private $logFile;

    protected function setUp()
    {
        $this->logFile = __DIR__.'/Fixtures/log.txt';
    }

    public function testConstructCreateFile()
    {
        unlink($this->logFile);
        $this->logger = new Logger($this->logFile);
        $this->assertTrue(file_exists($this->logFile));

        return $this->logger;
    }

    /**
     * @depends testConstructCreateFile
     */
    public function testLog(Logger $logger)
    {
        $logger->log('message');
        $this->assertRegexp('/\[(.*)\]\((.*)\) message/', file_get_contents($this->logFile));

        $this->tearDown();

        $logger->log('message', 'type');
        $this->assertRegexp('/\[(.*)\]\(type\) message/', file_get_contents($this->logFile));
    }

    public function testFormatCanBeOverride()
    {
        $logger = new MockLogger($this->logFile);
        $logger->log('message', 'type');
        $this->assertEquals('message_|_type', file_get_contents($this->logFile));
    }

    protected function tearDown()
    {
        file_put_contents($this->logFile, '');
    }
}
