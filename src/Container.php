<?php

declare(strict_types=1);

namespace Jnjxp\Container;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    protected $factories = [];

    protected $aliases = [];

    protected $instances = [];

    protected $extensions = [];

    public function __construct(
        array $factories = [],
        array $aliases = [],
        array $instances = [],
        array $extensions = []
    ) {
        $this->factories = $factories;
        $this->aliases = $aliases;
        $this->instances = $instances;
        $this->extensions = $extensions;
    }

    public function get($identity)
    {
        if (isset($this->instances[$identity])) {
            return $this->instances[$identity];
        }

        if (isset($this->aliases[$identity])) {
            return $this->fromAlias($identity);
        }

        if (isset($this->factories[$identity])) {
            return $this->fromFactory($identity);
        }

        return $this->fromNew($identity);
    }

    public function has($identity)
    {
        return isset($this->instances[$identity])
            || isset($this->factories[$identity])
            || isset($this->aliases[$identity]);
    }

    protected function fromAlias(string $identity)
    {
        $implementation = $this->aliases[$identity];
        $instance = $this->get($implementation);
        $this->instances[$identity] = $instance;
        return $instance;
    }

    protected function fromFactory(string $identity)
    {
        $factory  = $this->getFactory($this->factories[$identity]);
        $instance = $factory($this, $identity);
        $instance = $this->extend($identity, $instance);
        $this->instances[$identity] = $instance;
        return $instance;
    }

    protected function fromNew(string $identity)
    {
        $instance = new $identity;
        $instance = $this->extend($identity, $instance);
        $this->instances[$identity] = $instance;
        return $instance;
    }

    protected function extend(string $identity, $instance)
    {
        if (isset($this->extensions[$identity])) {
            foreach ($this->extensions[$identity] as $extension) {
                $extension = is_string($extension) ? $this->get($extension) : $extension;
                $instance = $extension($this, $instance);
            }
        }
        return $instance;
    }

    protected function getFactory($factory) : callable
    {
        if (is_string($factory)) {
            $factory = $this->get($factory);
        }

        if (is_array($factory) && is_string($factory[0])) {
            $factory[0] = $this->get($factory[0]);
        }

        return $factory;
    }
}
