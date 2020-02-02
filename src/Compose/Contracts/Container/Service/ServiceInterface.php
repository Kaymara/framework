<?php

namespace Compose\Contracts\Container\Service;

use Compose\Contracts\Container\ContainerAwareInterface;

interface ServiceInterface extends ContainerAwareInterface
{
    /**
     * Add a tag to the service.
     * 
     * @param string $id
     * 
     * @return self
     */
    public function tag(string $tag) : self;

    /**
     * Add many tags to the service
     *
     * @param array $tags
     * 
     * @return self
     */
    public function tagMany(array $tags) : self;

    /**
     * Has the service been tagged with given tag? 
     * 
     * @param string $tag
     * 
     * @return bool
     */
    public function taggedWith(string $tag) : bool;

    /**
     * Add an alias to the service.
     * 
     * @param string $alias
     * 
     * @return self
     */
    public function alias(string $alias) : self;

    /**
     * Add many aliases to the service.
     * 
     * @param array $aliases
     * 
     * @return self
     */
    public function aliasMany(array $aliases) : self;

    /**
     * Has the service been aliased with the given alias?
     * 
     * @param string $alias
     * 
     * @return bool
     */
    public function aliasedWith(string $alias) : bool;

    /**
     * Add argument to be injected
     *
     * @param mixed $arg
     * @return ServiceInterface
     */
    public function argument($arg) : ServiceInterface;

    /**
     * Add arguments to be injected
     *
     * @param array $args
     * @return ServiceInterface
     */
    public function arguments(array $args) : ServiceInterface;

    /**
     * Is the service a singleton?
     *
     * @return  boolean
     */ 
    public function isSingleton();

    /**
     * Set whether the service is a singleton
     *
     * @param  boolean  $singleton
     *
     * @return  self
     */ 
    public function singleton(bool $singleton = true) : ServiceInterface;

    /**
     * Set/Get concrete of the service
     *
     * @param \Closure|string|null $concrete
     * 
     * @return  mixed
     */ 
    public function concrete($concrete = null);

    /**
     * Resolve service
     *
     * @return mixed
     */
    public function resolve();
}