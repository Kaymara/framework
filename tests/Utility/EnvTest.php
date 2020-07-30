<?php declare(strict_types=1);

namespace Tests\Utility;

use Compose\Utility\Env;
use Dotenv\Repository\RepositoryInterface;
use PHPUnit\Framework\TestCase;

class EnvTest extends TestCase
{
    public function testGetRepository()
    {
        $this->assertInstanceOf(RepositoryInterface::class, Env::getRepository());
    }

    public function testGetVars()
    {
        $this->assertInstanceOf(RepositoryInterface::class, Env::getVars());
    }

    public function testDefaultIsReturnedIfValueNotFound()
    {
        $this->assertSame('bar', Env::get('foo', 'bar'));
    }

    public function testGetEnvVariable()
    {
        $_ENV['foo'] = 'bar';

        $this->assertSame('bar', Env::get('foo', 'baz'));
    }

    public function testVarRepoIsImmutable()
    {
        $this->assertSame('bar', Env::get('foo', 'baz'));

        Env::getVars()->set('foo', 'baz');

        $this->assertSame('bar', Env::get('foo', 'baz'));
    }

    public function testEnvRepoIsMutable()
    {
        $this->assertSame('bar', Env::get('foo', 'baz'));

        Env::getRepository()->set('foo', 'baz');

        $this->assertSame('baz', Env::get('foo', 'derek'));
    }

    public function testGetServerVariable()
    {
        $_SERVER['bar'] = 'baz';

        $this->assertSame('baz', Env::get('bar', 'derek'));
    }
}