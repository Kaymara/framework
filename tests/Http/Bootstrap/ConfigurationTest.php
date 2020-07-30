<?php declare(strict_types=1);

namespace Tests\Http\Bootstrap;

use Compose\Http\Application;
use Compose\Http\Bootstrap\Configuration;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    public \Compose\Contracts\Http\Application $app;
    public string $appConfig;

    public function setUp() : void
    {
        parent::setUp();

        $this->app = new Application(__DIR__);
        $this->appConfig = $this->app->configPath() . DIRECTORY_SEPARATOR . 'app.php';

        mkdir($this->app->configPath());
    }

    public function tearDown() : void
    {
        parent::tearDown();

        if (file_exists($this->appConfig)) {
            unlink($this->appConfig);
        }

        rmdir($this->app->configPath());
    }

    public function testBootstrap()
    {
        file_put_contents($this->appConfig, '<?php return ["foo" => "bar"];');

        (new Configuration())->bootstrap($this->app);

        $this->assertSame('bar', $this->app['config']->get('app.foo'));
    }

    public function testExceptionIsThrownWhenAppConfigNotFound()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unable to load the [app] configuration');

        (new Configuration())->bootstrap($this->app);
    }
}