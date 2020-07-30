<?php declare(strict_types=1);

namespace Compose\Events;

class Listener
{
    /**
     * The event bus instance
     *
     * @var EventBus
     */
    protected $bus;

    /**
     * The event that the listener is bound to
     *
     * @var string
     */
    protected $event;

    /**
     * The listener closure
     *
     * @var \Closure
     */
    protected $listener;

    /**
     * The listener's priority in the event queue
     *
     * @var int
     */
    protected $priority = 100;

    /**
     * Create a listener
     *
     * @param EventBus
     * @param string $event
     * @param \Closure $listener
     * @param int $priority
     */
    public function __construct($bus, $event, $listener, $priority = 100)
    {
        $this->bus = $bus;
        $this->event = $event;
        $this->listener = $listener;
        $this->priority = $priority;
    }

    /**
     * Create a listener
     *
     * @param EventBus $bus
     * @param string $event
     * @param \Closure $listener
     * @param int $priority
     *
     * @return Listener
     */
    public static function create($bus, $event, $listener, $priority = 100)
    {
        return new static($bus, $event, $listener, $priority);
    }

    /**
     * Get or set the listener's priority
     *
     * @param int|null $priority
     *
     * @return int
     */
    public function priority(int $priority = null)
    {
        if (is_null($priority)) {
            return $this->priority;
        }

        $this->priority = $priority;

        $this->bus->prioritize($this->event);
    }

    /**
     * Get listener closure
     *
     * @return \Closure
     */
    public function listener()
    {
        return $this->listener;
    }
}