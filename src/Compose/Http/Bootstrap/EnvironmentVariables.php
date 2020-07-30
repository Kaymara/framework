<?php

namespace Compose\Http\Bootstrap;

use Compose\Contracts\Http\Application;
use Compose\Contracts\Http\Bootstrappable;
use Compose\Utility\Env;
use Dotenv\Dotenv;
use Dotenv\Exception\InvalidFileException;

class EnvironmentVariables implements Bootstrappable
{
    /**
     * The application instance
     *
     * @var Application
     */
    public Application $app;

    /**
     * Bootstrap the environment variables
     *
     * @param Application $app
     *
     * @return void
     */
    public function bootstrap(Application $app)
    {
        if ($app->configCached()) {
            return;
        }

        $this->app = $app;

        $this->detectCustomEnvFile();

        // load environment-specific file, if it exists (e.g. .env.testing or .env.development)
        try {
            $this->createEnv()->safeLoad();
        } catch (InvalidFileException $e) {
            // todo: handle exception
        }
    }

    protected function createEnv()
    {
        return Dotenv::create(
            Env::getRepository(),
            $this->app->envPath(),
            $this->app->envFile()
        );
    }

    /**
     * Detect a custom env file exists matching the current environment
     *
     * @return void
     */
    protected function detectCustomEnvFile()
    {
        $env = Env::get('APP_ENV');

        if (is_null($env)) {
            return;
        }


        $customEnvFile = '.env.' . $env;

        if (file_exists($this->app->envPath() . DIRECTORY_SEPARATOR . $customEnvFile)) {
            $this->app->setEnvFile($customEnvFile);
        }
    }
}