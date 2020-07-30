<?php

namespace Compose\Traits;

trait CreateStaticInstance
{
    public static function create(...$args)
    {
        return new static(...$args);
    }
}