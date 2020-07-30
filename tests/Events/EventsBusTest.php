<?php declare(strict_types=1);

namespace Tests\Events;

use Compose\Container\Exception\ContainerException;
use Compose\Events\EventBus;
use Compose\Utility\Hash;
use \PHPUnit\Framework\TestCase;

class EventsBusTest extends TestCase
{
    /**
     * @param $listener
     *
     * @dataProvider listenerProvider
     */
    public function testListen($listener)
    {
        $bus = EventBus::create();
        $event = EventStub::class;

        $bus->listen($event, $listener);

        $this->assertCount(1, $listeners = $bus->getListeners($event));
        $this->assertInstanceOf(\Closure::class, Hash::get($listeners, 0));
    }

    public function listenerProvider()
    {
        return [
            'class'          => [ListenerStub::class],
            'closure'        => [fn () => 'foo'],
            'invokableClass' => [new InvokableListenerStub()]
        ];
    }

    /**
     * @param $listener
     * @param $payload
     * @param $expected
     *
     * @dataProvider broadcastProvider
     */
    public function testBroadcast($listener, $payload, $expected)
    {
        $bus = EventBus::create();
        $event = EventStub::class;

        $bus->listen($event, $listener);

        $responses = $bus->broadcast($event, $payload);

        $this->assertCount(1, $responses);
        $this->assertSame($expected, array_shift($responses));
    }

    public function broadcastProvider()
    {
        return [
            'class'             => [ListenerStub::class, ['foo'], 'foo'],
            'closure'           => [fn ($payload) => $payload, ['bar'], 'bar'],
            'invokableClass'    => [new InvokableListenerStub, ['baz'], 'baz'],
            'multiple payloads' => [AdditionStub::class, [1, 2], 3],
        ];
    }

    public function testBroadcastEventObject()
    {
        $bus = EventBus::create();
        $event = new InstanceStub;

        $bus->listen(InstanceStub::class, $listener = fn ($event) => $event->foo);

        $responses = $bus->broadcast($event);

        $this->assertCount(1, $responses);
        $this->assertSame($listener($event), array_shift($responses));
    }

    public function testCanHaltListenerQueue()
    {
        $bus = EventBus::create();

        $event = EventStub::class;

        $listener1 = function () {
        };
        $listener2 = fn () => 'bar';
        $listener3 = fn () => 'baz';

        $bus->listen($event, $listener1);
        $bus->listen($event, $listener2);
        $bus->listen($event, $listener3);

        $response = $bus->broadcast(EventStub::class, [], true);

        $this->assertSame($listener2(), $response);
    }

    public function testStopPropagation()
    {
        $bus = EventBus::create();

        $event = EventStub::class;

        $listener1 = function () {
        };
        $listener2 = fn () => false;
        $listener3 = fn () => 'baz';

        $bus->listen($event, $listener1);
        $bus->listen($event, $listener2);
        $bus->listen($event, $listener3);

        $responses = $bus->broadcast(EventStub::class, []);

        $this->assertCount(2, $responses);
        $this->assertSame($listener1(), array_shift($responses));
        $this->assertSame($listener2(), array_shift($responses));
    }

    public function testPrioritizeListeners()
    {
        $bus = EventBus::create();

        $event = EventStub::class;

        $listener1 = fn () => 'foo';
        $listener2 = fn () => 'bar';
        $listener3 = fn () => 'baz';

        $bus->listen($event, $listener1);
        $bus->listen($event, $listener2)->priority(1);
        $bus->listen($event, $listener3)->priority(2);

        $responses = $bus->broadcast(EventStub::class);

        $this->assertSame($listener2(), array_shift($responses));
        $this->assertSame($listener3(), array_shift($responses));
        $this->assertSame($listener1(), array_shift($responses));
    }

    public function testSubscribe()
    {
        $bus = EventBus::create();

        $bus->subscribe(SubscriberStub::class);

        $bus->broadcast(EventStub::class);

        $this->assertTrue(isset($_SERVER['foo']));
        $this->assertSame('bar', $_SERVER['foo']);
        unset($_SERVER['foo']);
        $this->assertTrue(isset($_SERVER['bar']));
        $this->assertSame('baz', $_SERVER['bar']);
        unset($_SERVER['bar']);
    }

    public function testSubscriberMustImplementSubscriberInterface()
    {
        $this->expectException(\InvalidArgumentException::class);

        $bus = EventBus::create();

        $bus->subscribe(EventStub::class);
    }

    public function testSubscriberMustBeClassNameOrObject()
    {
        $this->expectException(\InvalidArgumentException::class);

        $bus = EventBus::create();

        $bus->subscribe(1);
    }

    public function testSubscriberClassMustBeBuildable()
    {
        $this->expectException(ContainerException::class);

        $bus = EventBus::create();

        $bus->subscribe('foo');
    }
}

class EventStub
{
    //
}

class ListenerStub
{
    public function handle($payload)
    {
        return $payload;
    }
}

class InvokableListenerStub
{
    public function __invoke($payload)
    {
        return $payload;
    }
}

class AdditionStub
{
    public function handle($one, $two)
    {
        return $one + $two;
    }
}

class InstanceStub
{
    public $foo = 'bar';
}

class SubscriberStub implements \Compose\Events\Subscriber
{
    public function foo()
    {
        $_SERVER['foo'] = 'bar';
    }

    public function bar()
    {
        $_SERVER['bar'] = 'baz';
    }

    /**
     * @param EventBus $bus
     */
    public static function subscribe($bus)
    {
        $bus->listen(EventStub::class, static::class . '@foo');
        $bus->listen(EventStub::class, static::class . '@bar');
    }
}