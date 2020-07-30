<?php declare(strict_types=1);

namespace Compose\Log;

use Compose\Contracts\Events\EventBusInterface;
use Compose\Events\EventBus;
use Compose\Log\Events\MessageLogged;
use Psr\Log\LoggerInterface;

class Logger implements LoggerInterface
{
    /**
     * Logger instance
     * 
     * @var LoggerInterface 
     */
    protected $logger;

    /**
     * Event bus instance
     *
     * @var EventBusInterface
     */
    protected $eventBus;

    /**
     * Create a logger instance
     *
     * @param LoggerInterface $logger
     * @param EventBusInterface $eventBus
     *
     * @return void
     */
    public function __construct(LoggerInterface $logger, ?EventBusInterface $eventBus = null)
    {
        $this->logger = $logger;
        $this->eventBus = $eventBus;
    }

    /**
     * Create logger instance
     *
     * @param LoggerInterface $logger
     *
     * @param EventBus $eventBus
     *
     * @return self
     */
    public static function create(LoggerInterface $logger, EventBus $eventBus)
    {
        return new static($logger, $eventBus);
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function emergency($message, array $context = [])
    {
        $this->write(__FUNCTION__, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function alert($message, array $context = [])
    {
        $this->write(__FUNCTION__, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function critical($message, array $context = [])
    {
        $this->write(__FUNCTION__, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function error($message, array $context = [])
    {
        $this->write(__FUNCTION__, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function warning($message, array $context = [])
    {
        $this->write(__FUNCTION__, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function notice($message, array $context = [])
    {
        $this->write(__FUNCTION__, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function info($message, array $context = [])
    {
        $this->write(__FUNCTION__, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function debug($message, array $context = [])
    {
        $this->write(__FUNCTION__, $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log($level, $message, array $context = [])
    {
        $this->write(__FUNCTION__, $message, $context);
    }

    /**
     * Register listener with the MessageLogged event
     *
     * @param string|callable $listener
     *
     * @return void
     */
    public function listen($listener)
    {
        if (isset($this->eventBus)) {
            $this->eventBus->listen(MessageLogged::class, $listener);
        }
    }

    /**
     * Use this instance as a proxy for the underlying logger instance
     *
     * @param $method
     * @param $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->logger->{$method}(...$args);
    }

    /**
     * Write to the log
     *
     * @param string $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    protected function write(string $level, string $message, array $context = [])
    {
        $this->logger->{$level}($message, $context);

        $this->broadcastEvent($level, $message, $context);
    }

    /**
     * Broadcast an event when a message has been logged
     *
     * @param string $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    protected function broadcastEvent(string $level, string $message, array $context = [])
    {
        if (isset($this->eventBus)) {
            $this->eventBus->broadcast(new MessageLogged($level, $message, $context));
        }
    }

    /**
     * Get the event bus
     *
     * @return EventBusInterface
     */
    public function getEventBus()
    {
        return $this->eventBus;
    }

    /**
     * Set the event bus
     *
     * @param EventBusInterface $eventBus
     */
    public function setEventBus(EventBusInterface $eventBus)
    {
        $this->eventBus = $eventBus;
    }
}