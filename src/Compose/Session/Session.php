<?php

namespace Compose\Session;

use Compose\Traits\CreateStaticInstance;
use Compose\Utility\Hash;
use Compose\Utility\Str;

class Session
{
    use CreateStaticInstance;

    /**
     * The Session's identifier
     *
     * @var string $id
     */
    protected $id;

    /**
     * The Session's name
     *
     * @var string $name
     */
    protected $name;

    /**
     * The Session's attributes
     *
     * @var array $attributes
     */
    protected $attributes = [];

    /**
     * Create a session instance
     *
     * @param $name
     */
    public function __construct($name = null)
    {
        $this->name = $name;
    }

    /**
     * Get/Set Session name
     *
     * @param string|null $name
     *
     * @return string|void
     */
    public function name(string $name = null)
    {
        if (is_null($name)) {
            return $this->name;
        }

        $this->name = $name;
    }

    /**
     * Get Session identifier
     *
     * @return string
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * Set Session identifier
     *
     * @param string|null $id
     *
     * @return void
     */
    public function setID(string $id = null)
    {
        if (is_null($id)) {
            $id = $this->regenerateID();
        }

        $this->id = $id;
    }

    /**
     * Generate unique Session identifier
     *
     * @return string
     * @throws \Exception
     */
    protected function regenerateID()
    {
        return Str::random(32);
    }

    /**
     * Set Session attribute key, value pair
     *
     * @param string $key
     * @param $value
     *
     * @return self
     */
    public function set($key, $value)
    {
        Hash::set($this->attributes, $key, $value);

        return $this;
    }

    /**
     * Set Session attribute key, value pairs
     *
     * @param array $keys
     * @param null $value
     *
     * @return self
     */
    public function setMany(array $keys, $value = null)
    {
        foreach ($keys as $k => $v) {
            Hash::set($this->attributes, $k, $v);
        }

        return $this;
    }

    /**
     * Get Session attribute value from given key
     *
     * @param $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return Hash::get($this->attributes, $key);
    }
}