<?php declare(strict_types=1);

namespace Compose\Events;

use Compose\Container\Container;
use Compose\Contracts\Container\ContainerInterface;
use Compose\Contracts\Events\EventBusInterface;
use Compose\Utility\Hash;
use http\Exception\InvalidArgumentException;

class EventBus implements EventBusInterface
{
    /**
     * The container instance
     *
     * @var \Compose\Contracts\Container\ContainerInterface
     */
    protected $container;

    /**
     * The listeners registered with the event bus
     *
     * @var array
     */
    protected $listeners = [];

    /**
     * Create an event bus
     *
     * @param \Compose\Contracts\Container\ContainerInterface $container
     */
    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container ?? new Container;
    }

    /**
     * Create an event bus
     *
     * @param ContainerInterface|null $container
     *
     * @return EventBus
     */
    public static function create(ContainerInterface $container = null)
    {
        return new static($container);
    }

    /**
     * @param string $event
     * @param string|callable $listener
     *
     * @return Listener
     */
    public function listen(string $event, $listener)
    {
        $resolved = $this->makeListener($listener);

        $this->listeners[$event][] = $listener = Listener::create($this, $event, $resolved);

        return $listener;
    }

    /**
     * Create a listener
     *
     * @param string|callable $listener
     *
     * @return \Closure
     */
    protected function makeListener($listener)
    {
        if (is_string($listener)) {
            return $this->makeListenerClass($listener);
        }

        return function ($payload) use ($listener) {
            return $listener(...$payload);
        };
    }

    /**
     * Create a closure from a class name
     *
     * @param string $listener
     *
     * @return \Closure
     */
    protected function makeListenerClass(string $listener)
    {
        [$listener, $method] = $this->parseListenerClass($listener);

        return function ($payload) use ($listener, $method) {
            return call_user_func([$this->container->make($listener), $method], ...$payload);
        };
    }

    /**
     * Parse a listener's class and method
     *
     * @param string $listener
     *
     * @return array
     */
    protected function parseListenerClass($listener)
    {
        if (strpos($listener, '@') === false) {
            return [$listener, 'handle'];
        }

        return explode('@', $listener);
    }

    /**
     * Broadcast an event to its listeners
     *
     * @param $event
     * @param array $payload
     * @param bool $halt
     *
     * @return mixed
     */
    public function broadcast($event, $payload = [], $halt = false)
    {
        [$event, $payload] = $this->parseEvent($event, $payload);

        $responses = [];

        foreach ($this->getListeners($event) as $listener) {
            $response = $listener($payload);

            if ($halt && ! is_null($response)) {
                return $response;
            }

            $responses[] = $listener($payload);

            if ($response === false) {
                return $responses;
            }
        }

        return $responses;
    }

    /**
     * Register subscriber with event bus
     *
     * @param mixed $subscriber
     */
    public function subscribe($subscriber)
    {
        if (is_string($subscriber)) {
            $subscriber = $this->container->make($subscriber);
        }

        $this->validateSubscriber($subscriber);

        $subscriber::subscribe($this);
    }

    /**
     * Prioritize listener queue for given event
     *
     * @param string $event
     *
     * @return $this
     */
    public function prioritize(string $event)
    {
        $listeners = $this->getListeners($event, true);

        if (empty($listeners)) {
            return $this;
        }

        usort($listeners, fn ($a, $b) => $a->priority() <=> $b->priority());

        $this->listeners[$event] = $listeners;

        return $this;
    }

    /**
     * Retrieve the listeners for a given event
     *
     * @param string $event
     * @param bool $returnObject
     *
     * @return array|mixed
     */
    public function getListeners(string $event, $returnObject = false)
    {
        if (is_null($listeners = $this->listeners[$event] ?? null)) {
            return [];
        }

        return $returnObject ? $listeners : array_map(fn ($listener) => $listener->listener(), $listeners);
    }

    /**
     * Parse the given event
     *
     * @param $event
     * @param array $payload
     *
     * @return array
     */
    protected function parseEvent($event, array $payload)
    {
        if (is_object($event)) {
            [$payload, $event] = [[$event], get_class($event)];
        }

        return [$event, $payload];
    }

    /**
     * Ensure subscriber is valid
     *
     * @param mixed $subscriber
     */
    protected function validateSubscriber($subscriber)
    {
        if (! is_object($subscriber)) {
            throw new \InvalidArgumentException(
                sprintf('Event subscriber must be a string or object. %s provided',
                    gettype($subscriber)
                )
            );
        }

        if (! $subscriber instanceof Subscriber) {
            throw new \InvalidArgumentException(
                sprintf('Event subscriber must implement the %s interface.',
                    Subscriber::class
                )
            );
        }
    }
}