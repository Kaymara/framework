<?php

namespace Compose\Log;

use Compose\Contracts\Http\Application;
use Compose\Utility\Hash;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\ProcessableHandlerInterface;
use Monolog\Processor\ProcessorInterface;
use Psr\Log\LoggerInterface;

class LogManager
{
    /**
     * The application instance
     *
     * @var \Compose\Contracts\Http\Application
     */
    protected $app;

    /**
     * Available logging channels
     *
     * @var array
     */
    protected $channels;

    /**
     * Default channel
     *
     * @var string
     */
    protected $defaultChannel;

    /**
     * The logger instance
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Create a LogManager instance
     *
     * @param \Compose\Contracts\Http\Application $app
     *
     * @throws LoggerException
     * @throws \ReflectionException
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->channels = $this->app['config']['logging.channels'];
        $this->defaultChannel = $this->app['config']['logging.default'];

        $this->createLogger();
    }

    /**
     * Create a LogManager instance
     *
     * @param Application $app
     *
     * @return LogManager
     *
     * @throws LoggerException
     * @throws \ReflectionException
     */
    public static function create(Application $app)
    {
        return new static($app);
    }

    /**
     * Get logger instance
     *
     * @return LoggerInterface
     */
    public function logger()
    {
        return $this->logger;
    }

    /**
     * Create logger instance
     *
     * @return void
     *
     * @throws LoggerException
     * @throws \ReflectionException
     */
    protected function createLogger()
    {
        $config = $this->getDefaultChannelConfig();
        $handlers = (array) Hash::get($config, 'handlers', []);
        $resolvedHandlers = [];
        $processors = (array) Hash::get($config, 'processors', []);
        $resolvedProcessors = [];

        foreach ($handlers as $handlerConfig) {
            $resolvedHandlers[] = $this->resolveHandler($handlerConfig);
        }

        foreach ($processors as $processorConfig) {
            $resolvedProcessors[] = $this->resolveProcessor($processorConfig);
        }


        $this->logger = Logger::create(
            new \Monolog\Logger('file', $resolvedHandlers, $resolvedProcessors),
            $this->app['eventBus']
        );
    }

    /**
     * Get the default channel configuration
     *
     * @return array
     *
     * @throws LoggerException
     */
    protected function getDefaultChannelConfig()
    {
        if (! array_key_exists($this->defaultChannel, $this->channels)) {
            throw new LoggerException(sprintf('Channel [%s] not found in list of configured channels.', $this->defaultChannel));
        }

        return $this->channels[$this->defaultChannel];
    }

    /**
     * Create a handler from the given config
     *
     * @param array $config
     *
     * @return mixed
     *
     * @throws LoggerException
     * @throws \ReflectionException
     */
    protected function resolveHandler($config)
    {
        if (! $handler = Hash::get($config, 'name')) {
            throw new LoggerException(sprintf('You must provide a handler for the [%s] logging channel', $this->defaultChannel));
        }

        $handler = $this->reflectClass($handler, $config, HandlerInterface::class);

        if ($handler instanceof ProcessableHandlerInterface) {
            foreach (Hash::get($config, 'processors', []) as $processor) {
                $handler->pushProcessor($this->resolveProcessor($processor));
            }
        }

        if ($handler instanceof FormattableHandlerInterface && $formatter = Hash::get($config, 'formatter')) {
            $handler->setFormatter($this->resolveFormatter($formatter));
        }

        return $handler;
    }

    /**
     * @param $formatterConfig
     *
     * @return mixed
     *
     * @throws LoggerException
     * @throws \ReflectionException
     */
    protected function resolveFormatter($formatterConfig)
    {
        $formatter = Hash::get($formatterConfig, 'name');

        if (is_null($formatter)) {
            throw new LoggerException(sprintf('You must specify a formatter in the logger config.'));
        }

        if (is_string($formatter)) {
            return $this->reflectFormatter($formatter, $formatterConfig);
        }

        throw new LoggerException(sprintf('Formatter must be a class name. You provided: [%s].', gettype($formatter)));
    }

    /**
     * @param string $formatter
     * @param $config
     *
     * @return mixed
     *
     * @throws LoggerException
     * @throws \ReflectionException
     */
    protected function reflectFormatter(string $formatter, $config)
    {
        return $this->reflectClass($formatter, $config, FormatterInterface::class);
    }

    /**
     * @param array $processorConfig
     *
     * @return \Closure|mixed
     *
     * @throws \ReflectionException
     * @throws LoggerException
     */
    protected function resolveProcessor($processorConfig)
    {
        $processor = Hash::get($processorConfig, 'processor');

        if (is_null($processor)) {
            throw new LoggerException(sprintf('You must specify a processor in the logger config.'));
        }

        if ($processor instanceof \Closure) {
            return $processor;
        }

        if (is_string($processor)) {
            return $this->reflectProcessor($processor, $processorConfig);
        }

        throw new LoggerException(sprintf('Processor must be a closure or class name. You provided: [%s].', gettype($processor)));
    }

    /**
     * @param string $processor A processor class name
     * @param array $config
     *
     * @return mixed
     *
     * @throws \ReflectionException
     * @throws LoggerException
     */
    protected function reflectProcessor(string $processor, array $config)
    {
        return $this->reflectClass($processor, $config, ProcessorInterface::class);
    }

    /**
     * Reflect given class and configure
     *
     * @param string $class
     * @param array $config
     * @param string $interface
     *
     * @return mixed
     *
     * @throws LoggerException
     * @throws \ReflectionException
     */
    protected function reflectClass(string $class, array $config, string $interface)
    {
        $reflection = new \ReflectionClass($class);

        if (! $reflection->implementsInterface($interface)) {
            throw new LoggerException(sprintf(
                    'Class must be an instance of [%s]. You provided [%s]',
                    $interface,
                    $class
                )
            );
        }

        $constructor = $reflection->getConstructor();

        $constructorParams = $constructor->getParameters();

        $args = [];

        foreach ($constructorParams as $param) {
            if (! $param->isOptional() && ! array_key_exists($param->getName(), $config)) {
                throw new LoggerException(sprintf(
                        'Required parameter [%s] has not been configured for class [%s]',
                        $param->getName(),
                        $reflection->getName()
                    )
                );
            }

            if ($param->isOptional() && ! array_key_exists($param->getName(), $config)) {
                $args[] = $param->getDefaultValue();

                continue;
            }

            $args[] = $config[$param->getName()];
        }

        return $reflection->newInstanceArgs($args);
    }
}