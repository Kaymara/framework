<?php

namespace Compose\Events;

interface Subscriber
{
    /**
     * Subscriber to events
     *
     * @param EventBus $eventBus
     *
     * @return void
     */
    public static function subscribe($eventBus);
}