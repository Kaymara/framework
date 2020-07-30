<?php

namespace Compose\Log\Events;

class MessageLogged
{
    /**
     * The log level
     *
     * @var string
     */
    public $level;

    /**
     * The logged message
     *
     * @var string
     */
    public $message;

    /**
     * The log context
     *
     * @var array
     */
    public $context;

    /**
     * Create a new MessageLogged event
     *
     * @param string $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function __construct($level, $message, array $context = [])
    {
        $this->level = $level;
        $this->message = $message;
        $this->context = $context;
    }
}