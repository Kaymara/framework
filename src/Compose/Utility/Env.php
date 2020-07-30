<?php declare(strict_types=1);

namespace Compose\Utility;

use Dotenv\Repository\Adapter\EnvConstAdapter;
use Dotenv\Repository\Adapter\PutenvAdapter;
use Dotenv\Repository\Adapter\ServerConstAdapter;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Repository\RepositoryInterface;
use PhpOption\Option;

class Env
{
    /**
     * The Dotenv repository
     *
     * @var RepositoryInterface|null
     */
    protected static $repository;

    /**
     * An immutable Dotenv variable repository
     *
     * @var RepositoryInterface|null
     */
    protected static $vars;

    /**
     * Does the Dotenv instance have write access?
     *
     * @var bool $put
     */
    protected static $put = true;

    /**
     * Set whether the repository has putenv access
     *
     * @param bool $put
     *
     * @return void
     */
    public static function put(bool $put)
    {
        static::$put = $put;
        static::$repository = static::$vars = null;
    }

    /**
     * Get the environment repository
     *
     * @return RepositoryInterface|null
     */
    public static function getRepository()
    {
        if (is_null(static::$repository)) {
            static::$repository = static::buildRepository();
        }

        return static::$repository;
    }

    /**
     * Get the variable repository instance
     *
     * @return RepositoryInterface
     */
    public static function getVars()
    {
        if (is_null(static::$vars)) {
            static::$vars = static::buildRepository(true);
        }

        return static::$vars;
    }

    /**
     * Get an environment var value
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return Option::fromValue(static::getVars()->get($key))
            ->map(function ($value) {
                switch (mb_strtolower($value)) {
                    case 'false':
                        return false;
                    case 'true':
                        return true;
                    case 'null':
                        return null;
                }

                return $value;
            })
            ->getOrCall(function () use ($default) {
                return $default;
            });
    }

    /**
     * Build an environment repository
     *
     * @param bool $immutable
     * @param array $adapters
     *
     * @return RepositoryInterface
     */
    protected static function buildRepository(bool $immutable = false, array $adapters = null)
    {
        $adapters ??= static::defaultAdapters();

        $builder = RepositoryBuilder::create()->withReaders($adapters)->withWriters($adapters);

        if ($immutable) {
            $builder = $builder->immutable();
        }

        return $builder->make();
    }

    /**
     * Get the default adapters
     *
     * @return array
     */
    protected static function defaultAdapters()
    {
        $adapters = [new EnvConstAdapter(), new ServerConstAdapter()];

        if (static::$put) {
            $adapters[] = new PutenvAdapter();
        }

        return $adapters;
    }
}