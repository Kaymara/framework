<?php

namespace Compose\Contracts\Config;

interface Repository
{
    /**
     * Does the given config key exist?
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key) : bool;

    /**
     * Get a config value
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Set a config key, value pair
     *
     * @param string $key
     * @param mixed $value
     *
     * @return self
     */
    public function set(string $key, $value);

    /**
     * Prepend value to key
     *
     * @param string $key
     * @param $value
     *
     * @return self
     */
    public function prepend(string $key, $value);

    /**
     * Push value to end of key
     *
     * @param string $key
     * @param $value
     *
     * @return self
     */
    public function push(string $key, $value);

    /**
     * Remove a key and its value from the repo
     *
     * @param string $key
     *
     * @return self
     */
    public function remove(string $key);

    /**
     * Return all items in repository
     *
     * @return array
     */
    public function all();
}