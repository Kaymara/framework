<?php declare(strict_types = 1);

namespace Compose\Container;

use Closure;
use ArrayAccess;
use LogicException;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use Compose\Container\Exception\ContainerException;
use Compose\Contracts\Container\ContainerInterface;
use Compose\Container\Exception\ArgumentResolutionException;
use Compose\Contracts\Container\Service\ServiceProviderAggregateInterface;

class Container implements ArrayAccess, ContainerInterface 
{
    /**
     * A global instance of the container
     *
     * @var ContainerInterface
     */
    protected static $instance = null;

    /**
     * Services managed by the container
     *
     * @var array[]
     */
    protected $services;

    /**
     * Shared instances managed by the container
     *
     * @var array
     */
    protected $instances;

    /**
     * Methods managed by the container
     *
     * @var \Closure[]
     */
    protected $methods;

    /**
     * Extension closures managed by the container
     *
     * @var \Closure[]
     */
    protected $extenders;

    /**
     * Service parameter defautls overrides
     *
     * @var array
     */
    protected $paramDefaultsOverrides = [];

    /**
     * Registered tags in the container
     *
     * @var array[]
     */
    protected $tags;

    /**
     * Aliases registered with the container
     *
     * @var array
     */
    protected $aliases;

    /**
     * Aliases a given service is known by
     *
     * @var array
     */
    protected $serviceAliases;

    /**
     * Resolved services
     *
     * @var array
     */
    protected $resolved;

    /**
     * todo: I don't think the container needs to manage providers.
     * todo: it's the providers job to register services with the container.
     * todo: Providers will be stored in a config file and registered/booted during bootstrapping the application
     * Aggregate of service providers
     *
     * @var ServiceProviderAggregateInterface
     */
    // protected $providers;

    /**
     * {@inheritDoc}
     */
    public function service(string $alias, $concrete = null, $singleton = false) 
    {
        $concrete ??= $alias;

        $this->services[$alias] = compact('concrete', 'singleton');

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function bind(string $alias, $concrete = null, $singleton = false) 
    {
        return $this->service($alias, $concrete, $singleton);
    }

    /**
     * {@inheritDoc}
     */
    public function singleton(string $alias, $concrete = null) 
    {
        return $this->service($alias, $concrete, true);
    }

    /**
     * {@inheritDoc}
     */
    public function instance(string $alias, $instance, $singleton = false) 
    {
        $this->instances[$alias] = $instance;

        return $this;
    }

    /**
     * Register given tags with aliases
     *
     * @param array|string $aliases
     * @param array|string $tags
     * 
     * @return void
     */
    public function tag($aliases, $tags) 
    {
        foreach ((array) $tags as $tag) {
            if (! $this->tagged($tag)) {
                $this->tags[$tag] = [];
            }

            foreach ((array) $aliases as $alias) {
                $this->tags[$tag][] = $alias;
            }
        }
    }

    /**
     * Is the given tag registered with the container?
     *
     * @param string $tag
     * 
     * @return bool
     */
    public function tagged(string $tag) 
    {
        return isset($this->tags[$tag]);        
    }

    /**
     * Get the aliases with given tag
     *
     * @param string $tag
     * 
     * @return array
     */
    public function taggedAliases(string $tag)
    {
        return isset($this->tags[$tag]) ? $this->tags[$tag] : [];
    }

    /**
     * Resolve tagged services
     *
     * @param string $tag
     * 
     * @return array
     */
    public function makeTagged(string $tag) 
    {
        if (! $this->tagged($tag)) {
            return [];
        }

        $results = [];

        foreach ($this->tags[$tag] as $alias) {
            $results[] = $this->make($alias);
        }

        return $results;
    }

    /**
     * {@inheritDoc}
     */
    public function flush() 
    {
        $this->services = [];
        $this->instances = [];
        $this->methods = [];
        $this->tags = [];
        $this->resolved = [];

        return $this;
    }

    /**
     * Alias given binding
     *
     * @param string $binding
     * @param array|string $aliases
     * 
     * @return void
     * 
     * @throws LogicException
     */
    public function alias(string $binding, $aliases) 
    {
        $bindingSameAsAlias = ! empty(array_filter((array) $aliases, fn($alias) => $alias === $binding));

        if ($bindingSameAsAlias) {
            throw new LogicException("[{$binding}] cannot be aliased to itself.");
        }

        foreach ((array) $aliases as $alias) {
            $this->aliases[$alias] = $binding;
            $this->serviceAliases[$binding][] = $alias;
        }
    }

    /**
     * Are the given aliases registered with the container?
     *
     * @param string|array $alias
     * 
     * @return boolean
     */
    public function aliased($aliases)
    {
        return empty(array_filter((array) $aliases, fn($alias) => ! isset($this->aliases[$alias])));
    }

    /**
     * Get the aliases for the given binding
     *
     * @param string $binding
     * @return void
     */
    public function aliases(string $binding)
    {
        return isset($this->serviceAliases[$binding]) ? $this->serviceAliases[$binding] : [];
    }

    /**
     * Resolve the given alias 
     *
     * @param string $alias
     * 
     * @return mixed
     */
    public function make(string $alias, array $paramOverrides = []) 
    {
        return $this->resolve($alias, $paramOverrides);
    }

    /**
     * {@inheritDoc}
     * 
     * @throws ArgumentResolutionException
     */
    public function get($alias) 
    {
        return $this->resolve($alias);
    }

    /**
     * Resolve the given alias
     *
     * @param string $alias
     * @param array $paramOverrides
     * 
     * @return mixed
     */
    protected function resolve(string $alias, $paramOverrides = []) 
    {
        if (isset($this->instances[$alias])) {
            return $this->instances[$alias];
        }

        $alias = $this->getAlias($alias);

        $concrete = $this->getConcrete($alias);

        $this->paramDefaultsOverrides = $paramOverrides;

        $service = $this->build($concrete, $alias);

        $this->extendService($alias, $service);

        if ($this->isShared($alias)) {
            $this->instances[$alias] = $service;
        }

        $this->resolved[$alias] = true;

        $this->paramDefaultsOverrides = [];

        return $service;
    }

    /**
     * Extend service 
     *
     * @param string $alias
     * @param mixed $service
     * 
     * @return void
     */
    protected function extendService(string $alias, $service)
    {
        if (isset($this->extenders[$alias])) {
            foreach ($this->extenders[$alias] as $extender) {
                $extender($service, $this);
            }
        }
    }

    /**
     * Get the alias for a binding if exists
     *
     * @param string $binding
     * 
     * @return string
     */
    protected function getAlias(string $binding)
    {
        if (! isset($this->aliases[$binding])) {
            return $binding;
        }

        return $this->getAlias($this->aliases[$binding]);
    }

    /**
     * Get the concrete type for a given alias
     *
     * @param string $alias
     * 
     * @return mixed
     */
    protected function getConcrete(string $alias) 
    {
        if (isset($this->services[$alias])) {
            return $this->services[$alias]['concrete'];
        }

        return $alias;
    }

    /**
     * Is the given alias shared in the container?
     *
     * @param string $alias
     * 
     * @return boolean
     */
    protected function isShared(string $alias) 
    {
        return isset($this->instances[$alias]) ||
            (isset($this->services[$alias]['singleton']) 
            && $this->services[$alias]['singleton'] === true);
    }

    /**
     * Instantiate an instance of the given type
     *
     * @param string|\Closure $concrete
     * 
     * @throws ArgumentResolutionException
     * 
     * @return mixed
     */
    protected function build($concrete) 
    {
        // if the concrete type is a closure, then we will simply pass back the results
        // of it, this way devs can pass closures to enable finer tuning resolution of classes
        if ($concrete instanceof Closure) {
            return $concrete($this, $this->getParamDefaultsOverrides());
        }

        // at this point we can assume that a class has been passed in
        // and attempt to resolve it and it's dependencies
        return $this->resolveClass($concrete);
    }

    /**
     * Resolve a class and its dependencies recursively
     *
     * @param string $concrete
     * 
     * @return mixed
     * 
     * @throws ArgumentResolutionException
     * @throws ContainerException
     */
    protected function resolveClass(string $concrete) 
    {
        try {
            $reflector = new ReflectionClass($concrete);
        } catch (ReflectionException $e) {
            throw new ContainerException("Class [{$concrete}] does not exist.", 0, $e);
        }

        if (! $reflector->isInstantiable()) {
            throw new ContainerException("Class [{$concrete}] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        // if there is no constructor or the constructor doesn't have any arguments
        // we can safely instantiate the class
        if (is_null($constructor) || $constructor->getNumberOfParameters() === 0) {
            return new $concrete;
        }

        // if the constructor does have arguments, we have to resolve them recursively
        $resolved = $this->resolveArguments($constructor->getParameters());

        return $reflector->newInstanceArgs($resolved);
    }

    /**
     * Instantiate given arguments
     *
     * @param ReflectionParameter[] $arguments
     * 
     * @throws ArgumentResolutionException
     * 
     * @return array
     */
    protected function resolveArguments (array $arguments) 
    {
        $results = [];

        foreach ($arguments as $argument) {
            if ($this->hasParamDefaultsOverride($argument))
            {
                $results[] = $this->getParamDefaultOverride($argument);

                continue;
            }

            $results[] = is_null($argument->getClass()) 
            ? $this->resolvePrimitive($argument) 
            : $this->resolveClass($argument->getClass()->getName());
        }

        return $results;
    }

    /**
     * Does the given param have an override?
     *
     * @param ReflectionParameter $param
     * 
     * @return boolean
     */
    protected function hasParamDefaultsOverride(ReflectionParameter $param)
    {
        return array_key_exists($param->getName(), $this->paramDefaultsOverrides);
    }

    /**
     * Get the param default override for given alias and param
     *
     * @return array
     */
    protected function getParamDefaultsOverrides()
    {
        return ! empty($this->paramDefaultsOverrides) ? $this->paramDefaultsOverrides : [];
    }

    /**
     * Get override for given parameter
     *
     * @param ReflectionParameter $param
     * 
     * @return mixed
     */
    protected function getParamDefaultOverride(ReflectionParameter $param)
    {
        return $this->hasParamDefaultsOverride($param)
            ? $this->paramDefaultsOverrides[$param->getName()]
            : null;
    }

    /**
     * Resolve a primitive value using reflection
     *
     * @param ReflectionParameter $primitive
     * 
     * @return mixed
     */
    protected function resolvePrimitive(ReflectionParameter $primitive) 
    {
        if (! $primitive->isDefaultValueAvailable()) {
            throw new ArgumentResolutionException('Primitive type without default value cannot be resolved.');
        }

        return $primitive->getDefaultValue();
    }

    /**
     * {@inheritDoc}
     */
    public function has($alias) 
    {
        return isset($this->services[$alias]) ||
            isset($this->instances[$alias]) ||
            isset($this->aliases[$alias]);
    }

    /**
     * Get the global container instance
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Set the global container instance
     *
     * @param ContainerInterface|null $container
     * 
     * @return ContainerInteface|null
     */
    public function setContainer(ContainerInterface $container = null) 
    {
        return static::$instance = $container;
    }

    /**
     * Is the given alias registered with the container?
     *
     * @param mixed $alias
     * 
     * @return bool
     */
    public function offsetExists($alias)
    {
        return $this->has($alias);
    }

    /**
     * Get the value of given alias
     *
     * @param mixed $alias
     * 
     * @return mixed
     */
    public function offsetGet($alias) 
    {
        return $this->resolve($alias);
    }

    /**
     * Bind a value to an alias
     *
     * @param mixed $alias
     * @param mixed $value
     * 
     * @return void
     */
    public function offsetSet($alias, $value) 
    {
        $this->service($alias, $value);
    }

    /**
     * Unset a container binding
     *
     * @param mixed $alias
     * 
     * @return void
     */
    public function offsetUnset($alias)
    {
        unset($this->services[$alias], $this->instances[$alias], $this->resolved[$alias]);
    }

    /**
     * Bind extender to alias
     *
     * @param string $alias
     * @param \Closure $extender
     * 
     * @return self
     */
    public function extend(string $alias, \Closure $extender)
    {
        $alias = $this->getAlias($alias);

        if (isset($this->instances[$alias])) {
            $this->instances[$alias] = $extender($this->instances[$alias], $this);

            return $this;
        }

        $this->extenders[$alias][] = $extender;

        return $this;
    }

    /**
     * Bootstrap a service with parameter defaults overrides
     *
     * @param string $alias
     * @param array $defaults
     * 
     * @return self
     */
    public function with(string $alias, array $defaults)
    {
        $alias = $this->getAlias($alias);

        $this->clearParamDefaultsOverrides($alias);

        foreach ($defaults as $param => $value) {
            $this->paramDefaultsOverrides[$alias][$param] = $value;
        }

        return $this;
    }

    /**
     * with() proxy
     *
     * @param string $alias
     * @param array $defaults
     * 
     * @return self
     */
    public function defaults(string $alias, array $defaults)
    {
        return $this->with($alias, $defaults);
    }

    /**
     * Remove any parameter defaults overrides for given alias
     *
     * @param string $alias
     * 
     * @return void
     */
    protected function clearParamDefaultsOverrides(string $alias)
    {
        unset($this->paramDefaultsOverrides[$alias]);
    }

    /**
     * Dynamically get service from container
     *
     * @param string $alias
     * 
     * @return mixed
     */
    public function __get(string $alias)
    {
        return $this[$alias];
    }

    /**
     * Dynamically add service to container
     *
     * @param string $alias
     * 
     * @param mixed $value
     * 
     * @return void
     */
    public function __set(string $alias, $value) 
    {
        $this[$alias] = $value;
    }
}