<?php

namespace Compose\Http\Bootstrap;

use Compose\Contracts\Http\Application;
use Compose\Contracts\Http\Bootstrappable;
use Compose\Contracts\Http\Debug\ExceptionHandler;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\FatalThrowableError;

class ErrorHandling implements Bootstrappable
{
    public const RESERVED_MEMORY_ALLOCATION = 1024 * 10;

    /**
     * The application instance
     *
     * @var Application $app
     */
    public $app;

    /**
     * Reserved memory so that errors can still be displayed when memory has been exhausted
     *
     * @var string
     */
    public static $reservedMemory;

    /**
     * Bootstrap the given application
     *
     * @param Application $app
     *
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $this->app = $app;

        self::$reservedMemory = str_repeat('x', self::RESERVED_MEMORY_ALLOCATION);

        error_reporting(E_ALL);

        set_error_handler([$this, 'handleError']);

        set_exception_handler([$this, 'handleException']);

        register_shutdown_function([$this, 'shutdown']);

        ini_set('display_errors', $this->app->inEnvironment('testing') ? 'On' : 'Off');
    }

    /**
     * Convert errors handled by reporting into ErrorExceptions
     *
     * @param int $level
     * @param string $message
     * @param string $file
     * @param int $line
     * @param array $context
     *
     * @return bool|void
     *
     * @throws \ErrorException
     */
    public function handleError(int $level, string $message, string $file = '', $line = 0, $context = [])
    {
        if (! $this->errorCoveredByReporting($level)) {
            // This should never happen. But just in case ;-)
            return false;
        }

        throw new \ErrorException($message, 0, $level, $file, $line);
    }

    public function handleException(\Throwable $e)
    {
        // errors do not extend \Exception so we must convert them to FatalThrowableException
        if ($this->isErrorException($e)) {
            $e = new FatalThrowableError($e);
        }

        // make room for the rendered exception
        self::$reservedMemory = null;

        $this->getExceptionHandler()->report($e);

        $method = $this->app->inConsole() ? 'renderConsole' : 'renderHttp';

        $this->$method($e);
    }

    /**
     * Catch fatal errors during shutdown
     *
     * @return void
     */
    public function shutdown()
    {
        if ($error = error_get_last() && $this->fatalError($error['type'])) {
            $this->handleException(new FatalErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
        }
    }

    /**
     * Is the current error level handled by reporting?
     *
     * @param int $level
     *
     * @return bool
     */
    protected function errorCoveredByReporting(int $level)
    {
        return (bool) (error_reporting() & $level);
    }

    /**
     * Is the given Throwable an error exception?
     *
     * @param \Throwable $e
     *
     * @return bool
     */
    protected function isErrorException(\Throwable $e)
    {
        return ! $e instanceof \Exception;
    }

    /**
     * Get an exception handler instance
     *
     * @return \Compose\Contracts\Http\Debug\ExceptionHandler
     */
    protected function getExceptionHandler()
    {
        return $this->app->make(ExceptionHandler::class);
    }

    /**
     * Render a console exception
     *
     * @param \Exception $e
     *
     * @return void
     */
    protected function renderConsole(\Exception $e)
    {
        $this->getExceptionHandler()->renderConsole(new ConsoleOutput, $e);
    }

    /**
     * Render an Http exception
     *
     * @param \Exception $e
     *
     * @return void
     */
    protected function renderHttp(\Exception $e)
    {
        $this->getExceptionHandler()->render($this->app['request'], $e)->send();
    }

    /**
     * Is the error type fatal?
     *
     * @param $type
     *
     * @return bool
     */
    protected function fatalError($type)
    {
        return in_array($type, [
            E_PARSE, E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR
        ]);
    }
}