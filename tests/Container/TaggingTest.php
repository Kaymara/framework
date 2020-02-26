<?php declare(strict_types = 1);

namespace Compose\Tests\Container;

use PHPUnit\Framework\TestCase;
use Compose\Container\Container;
 
class TaggingTest extends TestCase
{
    public function testCanTagAlias()
    {
        $container = new Container;
        
        $container->tag('foo', 'bar');

        $this->assertTrue($container->tagged('bar'));

        $this->assertSame(['foo'], $container->taggedAliases('bar'));
    }

    public function testCanTagMultipleAliases()
    {
        $container = new Container;
        
        $container->tag(['foo', 'bar'], 'baz');

        $this->assertTrue($container->tagged('baz'));

        $this->assertSame(['foo', 'bar'], $container->taggedAliases('baz'));
    }

    public function testCanTagMultipleAliasesWithMultipleTags()
    {
        $container = new Container;
        
        $container->tag(['foo', 'bar'], ['baz', 'derek']);
        
        $this->assertTrue($container->tagged('baz'));

        $this->assertTrue($container->tagged('derek'));

        $this->assertSame(['foo', 'bar'], $container->taggedAliases('baz'));

        $this->assertSame(['foo', 'bar'], $container->taggedAliases('derek'));
    }

    public function testMakeTagged()
    {
        $container = new Container;

        $this->assertSame([], $container->makeTagged('foo'));

        $container->service('foo', ServiceTaggingStub::class);

        $container->service('bar', SecondServiceTaggingStub::class);

        $container->tag(['foo', 'bar'], 'baz');

        $resolved = $container->makeTagged('baz');

        $this->assertCount(2, $resolved);

        $this->assertInstanceOf(ServiceTaggingStub::class, $resolved[0]);

        $this->assertInstanceOf(SecondServiceTaggingStub::class, $resolved[1]);
    }
}

class ServiceTaggingStub
{
    //
}

class SecondServiceTaggingStub
{
    //
}