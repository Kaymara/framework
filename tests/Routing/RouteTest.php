<?php

namespace Test\Routing;

use Compose\Contracts\Container\ContainerInterface;
use Compose\Routing\Route;
use Compose\Routing\Router;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function testConstructor()
    {
        $route = Route::create(Router::create());

        $this->assertInstanceOf(Router::class, $route->router());
        $this->assertInstanceOf(ContainerInterface::class, $route->container());
    }
}