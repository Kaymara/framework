<?php declare(strict_types=1);

namespace Tests\Config;

use Compose\Config\Repository;
use PHPUnit\Framework\TestCase;

class RepositoryTest extends TestCase
{
    public \Compose\Contracts\Config\Repository $repo;

    public $stub;

    public function setUp(): void
    {
        $this->stub = new RepoStub;
        $this->repo = new Repository([
            'foo'    => 'bar',
            'bar'    => 'baz',
            'nested' => [
                'foo' => 'bar',
                'bar' => ['baz' => 'derek']
            ],
            'false'  => false,
            'stub'   => $this->stub
        ]);
    }

    public function testSet()
    {
        $this->repo->set('nested.derek', 'soriam');

        $this->assertSame(['foo' => 'bar', 'bar' => ['baz' => 'derek'], 'derek' => 'soriam'],
            $this->repo->get('nested'));
    }

    public function testOffsetSet()
    {
        $this->repo['nested.derek'] = 'soriam';

        $this->assertSame(['foo' => 'bar', 'bar' => ['baz' => 'derek'], 'derek' => 'soriam'],
            $this->repo->get('nested'));
    }

    /**
     * @dataProvider getProvider
     */
    public function testGet($key, $expected)
    {
        $this->assertSame($expected, $this->repo->get($key));
    }

    public function getProvider()
    {
        return [
            'flat'   => ['foo', 'bar'],
            'nested' => ['nested.foo', 'bar'],
        ];
    }

    public function test__construct()
    {
        $this->assertInstanceOf(\Compose\Contracts\Config\Repository::class, $this->repo);
    }

    /**
     * @dataProvider hasProvider
     */
    public function testHas($key, $expected)
    {
        $this->assertSame($expected, $this->repo->has($key));
    }

    public function hasProvider()
    {
        return [
            'flat'       => ['foo', true],
            'nested'     => ['nested.foo', true],
            'dne'        => ['derek', false],
            'dne nested' => ['nested.derek', false],
        ];
    }

    public function testAll()
    {
        $this->assertSame([
            'foo'    => 'bar',
            'bar'    => 'baz',
            'nested' => [
                'foo' => 'bar',
                'bar' => ['baz' => 'derek']
            ],
            'false'  => false,
            'stub'   => $this->stub
        ], $this->repo->all());
    }

    public function testRemove()
    {
        $this->repo->remove('foo');

        $this->assertSame([
            'bar'    => 'baz',
            'nested' => [
                'foo' => 'bar',
                'bar' => ['baz' => 'derek']
            ],
            'false'  => false,
            'stub'   => $this->stub
        ], $this->repo->all());

        $this->repo->remove('nested.bar.baz');

        $this->assertSame([
            'bar'    => 'baz',
            'nested' => [
                'foo' => 'bar',
                'bar' => []
            ],
            'false'  => false,
            'stub'   => $this->stub
        ], $this->repo->all());

        $this->repo->remove('nested.foo');

        $this->assertSame([
            'bar'    => 'baz',
            'nested' => [
                'bar' => []
            ],
            'false'  => false,
            'stub'   => $this->stub
        ], $this->repo->all());

        $this->repo->remove('nested');

        $this->assertSame([
            'bar'   => 'baz',
            'false' => false,
            'stub'  => $this->stub
        ], $this->repo->all());
    }

    /**
     * @dataProvider offsetGetProvider
     */
    public function testOffsetGet($key, $expected)
    {
        $this->assertSame($expected, $this->repo[$key]);
    }

    public function offsetGetProvider()
    {
        return [
            'flat'   => ['foo', 'bar'],
            'nested' => ['nested.foo', 'bar'],
        ];
    }

    public function testOffsetUnset()
    {
        unset($this->repo['foo']);

        $this->assertSame([
            'bar'    => 'baz',
            'nested' => [
                'foo' => 'bar',
                'bar' => ['baz' => 'derek']
            ],
            'false'  => false,
            'stub'   => $this->stub
        ], $this->repo->all());

        unset($this->repo['nested.bar.baz']);

        $this->assertSame([
            'bar'    => 'baz',
            'nested' => [
                'foo' => 'bar',
                'bar' => []
            ],
            'false'  => false,
            'stub'   => $this->stub
        ], $this->repo->all());

        unset($this->repo['nested.foo']);

        $this->assertSame([
            'bar'    => 'baz',
            'nested' => [
                'bar' => []
            ],
            'false'  => false,
            'stub'   => $this->stub
        ], $this->repo->all());

        unset($this->repo['nested']);

        $this->assertSame([
            'bar'   => 'baz',
            'false' => false,
            'stub'  => $this->stub
        ], $this->repo->all());
    }

    public function testPush()
    {

    }

    public function testPrepend()
    {

    }

    public function testOffsetExists()
    {

    }
}

class RepoStub
{
}
