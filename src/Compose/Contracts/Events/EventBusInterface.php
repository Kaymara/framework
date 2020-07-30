<?php

namespace Compose\Contracts\Events;

interface EventBusInterface
{
    // feature list:
        // register a listener with the dispatcher
    public function listen(string $event, $listener);
        // dispatch event
    public function broadcast($event, $halt = false);
        // register a subscriber
    public function subscribe($subscriber);

    /**
     * Retrieve the listeners for a given event
     *
     * @param string $event
     *
     * @return array|mixed
     */
    public function getListeners(string $event);
        // detect if event propagation has been stopped
        // allow for listeners / subscribers to fire off their own events (not MVP)
        // queued event listeners (not MVP)
}