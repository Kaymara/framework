<?php

namespace Compose\Http\Bootstrap;

use Compose\Config\Repository;
use Compose\Contracts\Http\Application;
use Compose\Contracts\Http\Bootstrappable;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Configuration implements Bootstrappable
{
    /**
     * The application instance
     *
     * @var Application $app
     */
    protected $app;

    /**
     * Load configuration
     *
     * @param Application $app
     *
     * @throws \Exception
     */
    public function bootstrap(Application $app)
    {
        $this->app = $app;

        // check to see if we have a cached version of the config file
        // and if we do load the config values from that, which will be quicker
        if (file_exists($cachedConfig = $app->cachedConfigPath())) {
            $items = require $cachedConfig;
        }

        $app->instance('config', $repo = new Repository($items ?? []));

        // next we will spin through all of the config files and load them into the config repo
        if (! $app->configCached()) {
            $this->loadConfigFiles($repo);
        }

        $app->setEnvironment(fn() => $repo->get('app.env', 'production'));

        date_default_timezone_set($repo->get('app.timezone', 'UTC'));

        mb_internal_encoding('UTF-8');
    }

    /**
     * Load the config files into the repo
     *
     * @param Repository $repo
     *
     * @throws \Exception
     */
    protected function loadConfigFiles(Repository $repo)
    {
        $files = $this->getConfigFiles();

        if (! isset($files['app'])) {
            throw new \Exception('Unable to load the [app] configuration');
        }

        foreach ($files as $path => $file) {
            $repo->set($path, require $file);
        }
    }

    /**
     * Get config files
     *
     * @return array
     */
    protected function getConfigFiles()
    {
        $files = [];
        $configPath = realpath($this->app->configPath());

        /** @var SplFileInfo $file */
        foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
            $nested = $this->nested($file, $configPath);

            $files[$nested . basename($file->getRealPath(), '.php')] = $file->getRealPath();
        }

        return $files;
    }

    /**
     * Get the nested directory relative to the base config directory of a config file
     *
     * @param SplFileInfo $file
     * @param $configPath
     *
     * @return mixed|string
     */
    protected function nested(SplFileInfo $file, $configPath)
    {
        if ($dir = trim(str_replace($configPath, '', $file->getPath()), DIRECTORY_SEPARATOR)) {
            $dir = str_replace(DIRECTORY_SEPARATOR, '.', $dir)  . '.';
        }

        return $dir;
    }
}