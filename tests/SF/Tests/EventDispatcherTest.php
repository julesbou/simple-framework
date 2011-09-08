<?php

namespace SF\Tests;

use SF\EventDispatcher;

class EventDispatcherTest extends \PHPUnit_Framework_TestCase
{
    private $eventDispatcher;

    public function setUp()
    {
        $this->eventDispatcher = new EventDispatcher();
    }

    public function testListenAndDispatch()
    {
        $phpunit = $this;
        $this->eventDispatcher->listen('event', function($event)use($phpunit) {
            $phpunit->assertEquals('value', $event['param']);
        });

        $this->eventDispatcher->dispatch('event', array('param' => 'value'));
    }

    public function testDispatchReturnValue()
    {
        $this->eventDispatcher->listen('event', function($event) {
            return 'dispatched';
        });

        $this->assertEquals('dispatched', $this->eventDispatcher->dispatch('event'));
    }

    public function testDispatchToUnknownEvent()
    {
        $this->eventDispatcher->dispatch('unknown');
    }
}
