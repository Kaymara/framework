<?php declare(strict_types=1);

namespace Tests\Log;

use Compose\Http\Aggregates\ServiceProviders;
use Compose\Http\Application;
use Compose\Http\Bootstrap\Configuration;
use Compose\Log\Logger;
use Compose\Log\LoggerException;
use Compose\Log\LogManager;
use Compose\Utility\Hash;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\ProcessorInterface;
use PHPUnit\Framework\TestCase;

class LogManagerTest extends TestCase
{
    public \Compose\Contracts\Http\Application $app;
    public string $config;
    public string $logFile;

    public function setUp(): void
    {
        parent::setUp();


        $this->app = new Application(__DIR__, ServiceProviders::create());
        $this->config = $this->app['path.config'] . DIRECTORY_SEPARATOR . 'logging.php';
        $this->logFile = $this->app['path.log'] . DIRECTORY_SEPARATOR . 'app.log';
        $this->app->bootstrap([Configuration::class]);
    }

    public function testConstructor()
    {
        $this->app['config']['logging.default'] = 'file';
        $this->assertInstanceOf(LogManager::class, LogManager::create($this->app));
    }

    public function testDefaultChannelMustExistInListOfChannels()
    {
        $this->expectException(LoggerException::class);
        $this->expectExceptionMessage('Channel [dne] not found in list of configured channels');

        $this->app['config']['logging.default'] = 'dne';
        $this->assertInstanceOf(LogManager::class, LogManager::create($this->app));
    }

    public function testDefaultLoggerIsCreatedFromConfig()
    {
        $this->app['config']['logging.default'] = 'file';
        $manager = LogManager::create($this->app);
        $logger = $manager->logger();
        $handlerConfig = $this->app['config']['logging.channels.file.handlers.0'];

        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertSame('file', $logger->getName());

        $this->assertCount(1, $logger->getHandlers());
        $this->assertInstanceOf(StreamHandler::class, $handler = Hash::get($logger->getHandlers(), '0'));

        $this->assertCount(2, $processors = $logger->getProcessors());
        $this->assertInstanceOf(IntrospectionProcessor::class, Hash::get($processors, '0'));
        $this->assertInstanceOf(\Closure::class, Hash::get($processors, '1'));

        $this->assertSame($handlerConfig['stream'], $handler->getUrl());
    }

    public function testHandlerMustBeProvidedInHandlerConfig()
    {
        $this->app['config']['logging.default'] = 'noHandler';

        $this->expectException(LoggerException::class);
        $this->expectExceptionMessage('You must provide a handler for the [noHandler] logging channel');

        LogManager::create($this->app);
    }

    public function testHandlerMustImplementHandlerInterface()
    {
        $this->app['config']['logging.default'] = 'wrongHandler';

        $this->expectException(LoggerException::class);
        $this->expectExceptionMessage(sprintf(
                'Class must be an instance of [%s]. You provided [%s]',
                HandlerInterface::class,
                Stub::class
            )
        );

        LogManager::create($this->app);
    }

    public function testHandlerRequiredParametersNeedToBeConfigured()
    {
        $this->app['config']['logging.default'] = 'noStream';

        $this->expectException(LoggerException::class);
        $this->expectExceptionMessage(sprintf(
                'Required parameter [stream] has not been configured for class [%s]',
                StreamHandler::class,
            )
        );

        LogManager::create($this->app);
    }

    public function testProcessorMustBeProvidedInProcessorConfig()
    {
        $this->app['config']['logging.default'] = 'noProcessor';

        $this->expectException(LoggerException::class);
        $this->expectExceptionMessage('You must specify a processor in the logger config.');

        LogManager::create($this->app);
    }

    public function testProcessorMustImplementClosureOrProcessorInterface()
    {
        $this->app['config']['logging.default'] = 'intProcessor';

        $this->expectException(LoggerException::class);
        $this->expectExceptionMessage('Processor must be a closure or class name. You provided: [integer].');

        LogManager::create($this->app);
    }

    public function testProcessorClassNameMustImplementProcessorInterface()
    {
        $this->app['config']['logging.default'] = 'wrongProcessor';

        $this->expectException(LoggerException::class);
        $this->expectExceptionMessage(sprintf(
                'Class must be an instance of [%s]. You provided [%s]',
                ProcessorInterface::class,
                Stub::class
            )
        );

        LogManager::create($this->app);
    }

    public function testHandlerCanBeConfiguredWithProcessor()
    {
        $this->app['config']['logging.default'] = 'handlerWithProcessor';

        $manager = LogManager::create($this->app);
        $logger = $manager->logger();
        $handler = Hash::get($logger->getHandlers(), '0');

        $this->assertInstanceOf(ProcessorInterface::class, $handler->popProcessor());
    }

    public function testHandlerCanBeConfiguredWithFormatter()
    {
        $this->app['config']['logging.default'] = 'handlerWithFormatter';

        $manager = LogManager::create($this->app);
        $logger = $manager->logger();
        $handler = Hash::get($logger->getHandlers(), '0');

        $this->assertInstanceOf(FormatterInterface::class, $handler->getformatter());
    }

    public function testFormatterMustBeProvidedInFormatterConfig()
    {
        $this->app['config']['logging.default'] = 'noFormatter';

        $this->expectException(LoggerException::class);
        $this->expectExceptionMessage('You must specify a formatter in the logger config.');

        LogManager::create($this->app);
    }

    public function testFormatterClassNameMustImplementFormatterInterface()
    {
        $this->app['config']['logging.default'] = 'wrongFormatter';

        $this->expectException(LoggerException::class);
        $this->expectExceptionMessage(sprintf(
                'Class must be an instance of [%s]. You provided [%s]',
                FormatterInterface::class,
                Stub::class
            )
        );

        LogManager::create($this->app);
    }
}

class Stub
{
    //
}
