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
     * @param mixed $concrete
     */
    public function __construct(string $alias, $concrete = null) {
        $this->aliases[] = $alias;
        $this->concrete = $concrete ?? $alias;
    }

    /**
     * {@inheritDoc}
     */
    public function tag(string $tag) {
        if (! $this->taggedWith($tag)) {
            $this->tag[] = $tag;
        }

        return $this;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function taggedWith(string $tag) {
        return in_array($tag, $this->tags);
    }

    /**
     * {@inheritDoc}
     */
    public function alias(string $alias) {
        if (! $this->aliasedWith($alias)) {
            $this->aliases[] = $alias;
        }

        return $this;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function aliasedWith(string $alias) {
        return in_array($alias, $this->aliases);
    }

    /**
     * {@inheritDoc}
     */
    public function argument($arg) {
        $this->arguments[] = $arg;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function arguments(array $args) {
        foreach ($args as $arg) {
            $this->argument($arg);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */ 
    public function isSingleton()
    {
        return $this->singleton;
    }

    /**
     * {@inheritDoc}
     */ 
    public function singleton(bool $singleton = true) : ServiceInterface
    {
        $this->singleton = $singleton;

        return $this;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
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

        // todo: call any resolution methods that have been set on the service

        // todo: apply any service extenders

        // todo: set the instsance to the concrete

        // todo: return the instance
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
        try {
            $reflector = new ReflectionClass($concrete);
        } catch (ReflectionException $e) {
            throw new ServiceException("Class {$concrete} does not exist.", 0, $e);
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