<?php

namespace Compose\Config;

use Compose\Contracts\Config\Repository as RepositoryInterface;
use Compose\Utility\Hash;
use Tightenco\Collect\Support\Collection;

class Repository implements RepositoryInterface, \ArrayAccess
{
    /**
     * Config items
     *
     * @var array
     */
    protected array $items = [];

    /**
     * Create repository.
     *
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $key): bool
    {
        // search the repo by the "dot" notation key
        // and return if the key exists or not
        return Hash::has($this->items, $key);
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key, $default = null)
    {
        return Hash::get($this->items, $key, $default);
    }

    /**
     * Set a config key, value pair
     *
     * @param string $key
     * @param mixed $value
     *
     * @return self
     */
    public function set(string $key, $value)
    {
        Hash::set($this->items, $key, $value);

        return $this;
    }

    /**
     * Prepend value to key
     *
     * @param string $key
     * @param $value
     *
     * @return self
     */
    public function prepend(string $key, $value)
    {
        // TODO: Implement prepend() method.
    }

    /**
     * Push value to end of key
     *
     * @param string $key
     * @param $value
     *
     * @return self
     */
    public function push(string $key,$value){
     // TODO: Implement push() method.
    }

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        return static::get($offset);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
        static::set($offset, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($offset)
    {
        static::remove($offset);
    }

    /**
     * Remove a key and its value from the repo
     *
     * @param string $key
     *
     * @return self
     */
    public function remove(string $key)
    {
        Hash::remove($this->items, $key);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function all()
    {
        return $this->items;
    }
}
