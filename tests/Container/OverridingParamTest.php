<?php declare(strict_types = 1);

namespace Compose\Tests\Container;

use PHPUnit\Framework\TestCase;
use Compose\Container\Container;
 
class OverridingParamTest extends TestCase
{
    public function testOverrideParamWithoutDefault()
    {
        $container = new Container;

        $container->service(ServiceStubOverridingParamWithoutDefault::class);

        $service = $container->make(ServiceStubOverridingParamWithoutDefault::class, ['age' => 25]);

        $this->assertSame(25, $service->age);
    }

    public function testClosureOverrides()
    {
        $container = new Container;

        $container->service('foo', function ($c, $params) {
            return $params;
        });

        $this->assertSame(['age' => 25], $container->make('foo', ['age' => 25]));
    }

    public function testNestedOverrides()
    {
        $container = new Container;

        $container->service('foo', function ($c) {
            return $c->make('bar', ['name' => 'Derek']);
        });

        $container->service('bar', function ($c, $params) {
            return $params;
        });

        $this->assertSame(['name' => 'Derek'], $container->make('foo'));
    }

    public function testOverrideMultipleTimes()
    {
        $container = new Container;

        $container->service('foo', function ($c, $params) {
            return $params;
        });

        $this->assertSame(['name' => 'Derek'], $container->make('foo', ['name' => 'Derek']));
        $this->assertSame(['name' => 'Soriam'], $container->make('foo', ['name' => 'Soriam']));
        $this->assertSame(['name' => 'Amara'], $container->make('foo', ['name' => 'Amara']));
    }
}

class ServiceStubOverridingParamWithoutDefault
{
    public $age;

    public function __construct(int $age)
    {
        $this->age = $age;
    }
}