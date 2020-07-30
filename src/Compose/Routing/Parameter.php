<?php

namespace Compose\Routing;

class Parameter
{
    /**
     * The parameter name
     *
     * @var string
     */
    protected $name;

    /**
     * The parameter value
     *
     * @var mixed
     */
    protected $value;

    /**
     * Is the parameter required?
     *
     * @var bool
     */
    protected $required;

    /**
     * The bound pcre
     *
     * @var string
     */
    protected $pattern;

    /**
     * Create a route parameter instance
     *
     * @param $name
     * @param bool $required
     */
    public function __construct($name, $required = true)
    {
        $this->name = $name;
        $this->required = $required;
    }

    /**
     * Create a route parameter instance
     *
     * @param $name
     * @param bool $required
     *
     * @return self
     */
    public static function create($name, $required = true)
    {
        return new static($name, $required);
    }

    /**
     * Get the parameter's name
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Get or set whether the parameter is required
     *
     * @param null $required
     *
     * @return bool|self
     */
    public function required($required = null)
    {
        if (is_null($required)) {
            return $this->required;
        }

        $this->required = $required;

        return $this;
    }

    /**
     * Get or set parameter value
     *
     * @param null $value
     *
     * @return mixed|self
     */
    public function value($value = null)
    {
        if (is_null($value)) {
            return $this->value;
        }

        $this->value = $value;

        return $this;
    }

    /**
     * Get or set the parameter's pcre
     *
     * @param string|null $pattern
     *
     * @return string
     */
    public function pattern(string $pattern = null)
    {
        if (is_null($pattern)) {
            return $this->pattern;
        }

        $this->pattern = $pattern;
    }
}