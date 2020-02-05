<?php

namespace Compose\Container\Service;

use Closure;
use Compose\Container\Exception\ContainerException;
use Compose\Container\Exception\ServiceException;
use Compose\Container\Traits\ContainerAware;
use Compose\Contracts\Container\Service\ServiceInterface;
use ReflectionClass;
use ReflectionException;

class Service implements ServiceInterface
{
    use ContainerAware;

    /**
     * Instance of the service
     *
     * @var mixed
     */
    protected $instance;

    /**
     * Concrete of the service
     *
     * @var mixed
     */
    protected $concrete;

    /**
     * Whether the service is a singleton
     *
     * @var boolean
     */
    protected $singleton = false;

    /**
     * Tags that the service is tagged with
     *
     * @var array
     */
    protected $tags = [];

    /**
     * Aliases that the service is known by
     * 
     * @var array
     */
    protected $aliases = [];

    /**
     * Arguments that belong to the service
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * Service extenders
     *
     * @var array
     */
    protected $extenders = [];

    /**
     * @var array
     */
    protected $methods = [];

    /**
     * Service constructor
     *
     * @param string $alias
     * @param \Closure|string|null $concrete
     */
    public function __construct(string $alias, $concrete = null) {
        $this->aliases[] = $alias;
        $this->concrete = $concrete ?? $alias;
    }

    /**
     * Add a tag to the service.
     * 
     * @param string $id
     * 
     * @return self
     */
    public function tag(string $tag) {
        if (! $this->taggedWith($tag)) {
            $this->tag[] = $tag;
        }

        return $this;
    }

    /**
     * Add many tags to the service
     *
     * @param array $tags
     * 
     * @return self
     */
    public function tagMany(array $tags) {
        foreach ($tags as $tag) {
            if (! $this->taggedWith($tag)) {
                $this->tag[] = $tag;
            }
        }

        return $this;
    }

    /**
     * Has the service been tagged with given tag? 
     * 
     * @param string $tag
     * 
     * @return bool
     */
    public function taggedWith(string $tag) {
        return in_array($tag, $this->tags);
    }

    /**
     * Add an alias to the service.
     * 
     * @param string $alias
     * 
     * @return self
     */
    public function alias(string $alias) {
        if (! $this->aliasedWith($alias)) {
            $this->aliases[] = $alias;
        }

        return $this;
    }

    /**
     * Add many aliases to the service.
     * 
     * @param array $aliases
     * 
     * @return self
     */
    public function aliasMany(array $aliases) {
        foreach ($aliases as $alias) {
            if (! $this->aliasedWith($alias)) {
                $this->aliases[] = $alias;
            }

            return $this;
        }
    }

    /**
     * Has the service been aliased with the given alias?
     * 
     * @param string $alias
     * 
     * @return bool
     */
    public function aliasedWith(string $alias) {
        return in_array($alias, $this->aliases);
    }

    /**
     * Add argument to be injected
     *
     * @param mixed $arg
     * 
     * @return ServiceInterface
     */
    public function argument($arg) {
        $this->arguments[] = $arg;

        return $this;
    }

    /**
     * Add arguments to be injected
     *
     * @param array $args
     * 
     * @return ServiceInterface
     */
    public function arguments(array $args) {
        foreach ($args as $arg) {
            $this->argument($arg);
        }

        return $this;
    }

    /**
     * Is the service a singleton?
     *
     * @return  boolean
     */ 
    public function isSingleton()
    {
        return $this->singleton;
    }

    /**
     * Set whether the service is a singleton
     *
     * @param  boolean  $singleton
     *
     * @return  self
     */ 
    public function singleton(bool $singleton = true) : ServiceInterface
    {
        $this->singleton = $singleton;

        return $this;
    }

    /**
     * Set/Get concrete of the service
     *
     * @param  \Closure|string|null  $concrete
     * 
     * @return  mixed
     */ 
    public function concrete($concrete = null)
    {
        if (is_null($concrete)) {
            return $this->concrete;
        }

        $this->concrete = $concrete;
        $this->instance = null;

        return $this;
    }

    /**
     * Add method call to service
     *
     * @param string $name
     * @param array $arguments
     * 
     * @return self
     */
    public function addMethod(string $name, array $arguments = []) {
        $this->methods[] = compact('name', 'arguments');

        return $this;
    }

    /**
     * Add method calls to service
     *
     * @param array $methods
     * 
     * @return self
     */
    public function addMethods(array $methods) {
        foreach ($methods as $method => $args) {
            $this->addMethod($method, $args);
        }

        return $this;
    }

    /**
     * Resolve service
     *
     * @return mixed
     */
    public function resolve() {
        $concrete = $this->concrete;

        // if the service is a singleton and has already been resolved, return instance
        if ($this->isSingleton() && ! is_null($this->instance)) {
            return $this->instance;
        }
        
        // if the concrete is a closure, resolve the closure
        if ($concrete instanceof Closure) {
            $concrete = $this->resolveClosure($concrete);
        }

        if (is_string($concrete)) {
            $this->resolveClass($concrete);
        }

        // call any resolution methods that have been set on the service

        // apply any service extenders

        // set the instsance to the concrete

        // return the instance
    }

    /**
     * Resolve a closure
     *
     * @param \Closure $closure
     * 
     * @return mixed
     */
    protected function resolveClosure(Closure $closure) {
        $dependencies = $this->resolveArguments($this->arguments);

        return $closure($dependencies);
    }

    protected function resolveClass(string $concrete) {
        $concrete = $this->concrete;

        try {
            $reflector = new ReflectionClass($concrete);
        } catch (ReflectionException $e) {
            throw new ServiceException("Class \"$concrete\" does not exist.", 0, $e);
        }

        if (! $reflector->isInstantiable()) {
            // bail
        }

        if (empty($this->arguments)) {
            return new $concrete;
        }

        $resolved = $this->resolveArguments($this->arguments);

        return $reflector->newInstanceArgs($resolved);
    }

    protected function resolveArguments($args) {
        $resolved = [];

        try {
            $container = $this->getContainer();
        } catch (ContainerException $e) {
            throw $e;
        }

        foreach ($args as $arg) {
            if ($container->has($arg)) {
                $resolved[] = $container->get($arg);
            }
        }

        return $resolved;
    }
}