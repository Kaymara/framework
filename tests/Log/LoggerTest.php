<?php declare(strict_types=1);

namespace Tests\Log;

use Compose\Events\EventBus;
use Compose\Log\Events\MessageLogged;
use Compose\Log\Logger;
use Mockery as M;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    public function tearDown() : void
    {
        m::close();
    }

    public function testMethodsAreProxiedToMonolog()
    {
        $this->expectNotToPerformAssertions();

        $logger = new Logger($monolog = m::mock(\Monolog\Logger::class)->makePartial());

        $monolog->shouldReceive('debug')->once()->with('foo', []);
        $monolog->shouldReceive('foo')->once();

        $logger->debug('foo');
        $logger->foo();
    }

    public function testEventFiredWhenMessageLogged()
    {
        $logger = new Logger(
            $monolog = m::mock(\Monolog\Logger::class)->makePartial(),
            $bus = new EventBus()
        );

        $monolog->shouldReceive('debug')->once()->with('foo', []);

        $bus->listen(MessageLogged::class, function ($event) {
            $_SERVER['level'] = $event->level;
            $_SERVER['message'] = $event->message;
            $_SERVER['context'] = $event->context;
        });

        $logger->debug('foo');

        $this->assertTrue(isset($_SERVER['level']));
        $this->assertSame('debug', $_SERVER['level']);
        unset($_SERVER['level']);

        $this->assertTrue(isset($_SERVER['message']));
        $this->assertSame('foo', $_SERVER['message']);
        unset($_SERVER['message']);

        $this->assertTrue(isset($_SERVER['context']));
        $this->assertSame([], $_SERVER['context']);
        unset($_SERVER['context']);
    }

    public function testCanRegisterListenerWithLoggerEvent()
    {
        $logger = new Logger(
            m::mock(\Monolog\Logger::class)->makePartial(),
            new EventBus()
        );

        $logger->listen(fn($payload) => $payload);

        $this->assertCount(1, $logger->getEventBus()->getListeners(MessageLogged::class));
    }
}