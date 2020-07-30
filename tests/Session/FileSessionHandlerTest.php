<?php

namespace Test\Session;

use Compose\Http\Aggregates\ServiceProviders;
use Compose\Http\Application;
use Compose\Session\FileSessionHandler;
use Compose\Utility\Str;
use League\Flysystem\Adapter\Local;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use PHPUnit\Framework\TestCase;

class FileSessionHandlerTest extends TestCase
{
    private \Compose\Contracts\Http\Application $app;
    private FilesystemInterface $files;
    private ?string $tmpDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Application(__DIR__, $providers = ServiceProviders::create());
        $this->files = new Filesystem(new Local(__DIR__));
        $this->tmpDir = __DIR__ . DIRECTORY_SEPARATOR . 'tmp';

        $this->files->createDir($this->tmpDir);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->files->deleteDir($this->tmpDir);

        $this->tmpDir = null;
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(FileSessionHandler::class, FileSessionHandler::create($this->files, $this->tmpDir));
    }

    public function testWritesToFile()
    {
        $handler = FileSessionHandler::create($this->files, $this->tmpDir);
        $sessionId = Str::random();
        $sessionFile = $this->tmpDir . DIRECTORY_SEPARATOR . $sessionId;

        $handler->write($sessionId, 'foo');

        $this->assertEquals('foo', $this->files->read($sessionFile));
    }

    public function testThrowsExceptionOnReadWhenFileDoesNotExist()
    {
        $this->expectException(FileNotFoundException::class);

        $handler = FileSessionHandler::create($this->files, $this->tmpDir);

        $handler->read('foo.txt');
    }

    public function testReadsDataFromSessionFile()
    {
        $handler = FileSessionHandler::create($this->files, $this->tmpDir);
        $sessionId = Str::random();

        $handler->write($sessionId, 'foo');

        $this->assertSame('foo', $handler->read($sessionId));
    }

    // can read key from session file

    // can garbage collect session files
}