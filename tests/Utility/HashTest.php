<?php declare(strict_types=1);

namespace Tests\Utility;

use Compose\Utility\Hash;
use PHPUnit\Framework\TestCase;

class HashTest extends TestCase
{
    /**
     * @dataProvider accessibleProvider
     */
    public function testAccessible($value, $expectation)
    {
        $this->assertSame($expectation, Hash::accessible($value));
    }

    public function accessibleProvider()
    {
        return [
            [[], true],
            [new AccessibleStub(), true],
            [new \stdClass(), false],
            ['foo', false],
        ];
    }

    /**
     * @dataProvider existsProvider
     */
    public function testExists($value, $key, $expectation)
    {
        $this->assertSame($expectation, Hash::exists($value, $key));
    }

    public function existsProvider()
    {
        return [
            [['foo' => 'bar'], 'foo', true],
            [['bar' => 'foo'], 'foo', false],
            [new AccessibleStub(), 'foo', true],
            [new AccessibleStub(), 'bar', false],
        ];
    }

    /**
     * @dataProvider hasProvider
     */
    public function testHas($value, $key, $expectation)
    {
        $this->assertSame($expectation, Hash::has($value, $key));
    }

    public function hasProvider()
    {
        return [
            [['foo' => 'bar'], 'foo', true],
            [['foo' => ['bar' => 'baz']], 'foo.bar', true],
            [['foo' => ['bar' => ['baz' => ['derek', 'soriam', 'amara']]]], 'foo.bar.baz', true],
            [[], 'foo', false],
            [['foo' => 'bar'], '', false],
            [['foo' => ['bar' => ['baz' => ['derek', 'soriam', 'amara']]]], 'foo.bar.derek', false],
        ];
    }

    /**
     * @dataProvider getProvider
     */
    public function testGet($array, $key, $default, $expected)
    {
        $this->assertSame($expected, Hash::get($array, $key, $default));
    }

    public function getProvider()
    {
        return [
            'not accessible' => [new NotAccessibleStub, 0, null, null],
            'key is null' => [['foo'], null, null, null],
            'numeric key' => [['foo'], 0, null, 'foo'],
            'numeric key not found' => [['foo' => 'bar', 1 => 'baz'], 0, 'derek', 'derek'],
            'string key' => [['foo' => 'bar', 1 => 'baz'], 'foo', 'derek', 'bar'],
            'nested key' => [['foo' => ['bar' => ['derek' => ['soriam', 'amara']], 'baz']], 'foo.bar.derek', null, ['soriam', 'amara']],
            'array access' => [new AccessibleStub(), 'foo', null, 'bar'],
            'nested key array access' => [new AccessibleStub(), 'nested.foo.bar', null, 'baz'],
        ];
    }

    /**
     * @dataProvider setProvider
     */
    public function testSet($array, $key, $value, $expected)
    {
        $this->assertSame($expected, Hash::set($array, $key, $value));
    }

    public function setProvider()
    {
        return [
            'non-accessible' => [new NotAccessibleStub(), 'foo.bar', 'baz', null],
            'null key' => [['foo' => 'bar'], null, 'baz', 'baz'],
            'flat key' => [['foo' => 'bar'], 'baz', 'derek', ['foo' => 'bar', 'baz' => 'derek']],
            'nested key' => [[], 'foo.bar.baz', 'derek', ['baz' => 'derek']],
        ];
    }

    public function testSetIsDestructive()
    {
        $array = [];
        Hash::set($array, 'foo.bar.baz', 'derek');

        $this->assertSame(['foo' => ['bar' => ['baz' => 'derek']]], $array);
    }

    public function testRemove()
    {
        $array = [
            'foo' => 'bar',
            'bar' => ['baz' => ['derek', 'soriam']],
        ];

        Hash::remove($array, 'foo');

        $this->assertSame(['bar' => ['baz' => ['derek', 'soriam']]], $array);

        Hash::remove($array, 'bar.baz.0');

        $this->assertSame(['bar' => ['baz' => [1 => 'soriam']]], $array);

        Hash::remove($array, 'bar.baz');

        $this->assertSame(['bar' => []], $array);

        Hash::remove($array, 'bar');

        $this->assertSame([], $array);
    }

    public function testFlatten()
    {
        // todo: implement test
    }
}

class AccessibleStub implements \ArrayAccess
{
    public $foo = 'bar';
    public $nested = ['foo' => ['bar' => 'baz']];

    public function offsetExists($offset)
    {
        return property_exists($this, $offset);
    }

    public function offsetGet($offset) {
        return $this->$offset;
    }

    public function offsetSet($offset, $value) {}

    public function offsetUnset($offset) {}
}

class NotAccessibleStub {}