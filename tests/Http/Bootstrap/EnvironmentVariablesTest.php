<?php

namespace Tests\Http\Bootstrap;

use Compose\Http\Application;
use Compose\Http\Bootstrap\EnvironmentVariables;
use PHPUnit\Framework\TestCase;

class EnvironmentVariablesTest extends TestCase
{
    public function testBootstrap()
    {
        $app = new Application(__DIR__);

        file_put_contents($app->envPath() . DIRECTORY_SEPARATOR . '.env', 'FOO=bar' . PHP_EOL);

        (new EnvironmentVariables())->bootstrap($app);

        $this->assertArrayHasKey('FOO', $_ENV);
        $this->assertSame('bar', $_ENV['FOO']);
        $this->assertArrayHasKey('FOO', $_SERVER);
        $this->assertSame('bar', $_SERVER['FOO']);

        unlink($app->envPath() . DIRECTORY_SEPARATOR . '.env');
    }

    public function testCustomEnvFileIsUsed()
    {
        $app = new Application(__DIR__);

        file_put_contents($app->envPath() . DIRECTORY_SEPARATOR . '.env', 'FOO=bar' . PHP_EOL);
        file_put_contents($app->envPath() . DIRECTORY_SEPARATOR . '.env.testing', 'FOO=baz' . PHP_EOL);

        $_ENV['APP_ENV'] = 'testing';

        (new EnvironmentVariables())->bootstrap($app);

        $this->assertArrayHasKey('FOO', $_ENV);
        $this->assertSame('baz', $_ENV['FOO']);
        $this->assertArrayHasKey('FOO', $_SERVER);
        $this->assertSame('baz', $_SERVER['FOO']);

        unlink($app->envPath() . DIRECTORY_SEPARATOR . '.env');
        unlink($app->envPath() . DIRECTORY_SEPARATOR . '.env.testing');
    }
}