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

    public function get(string $identity)
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

    public function has(string $identity) : bool
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
                $extension = $this->getExtension($extension);
                $instance = $extension($this, $instance);
            }
        }
        return $instance;
    }

    protected function getCallable($spec) : callable
    {
        if (is_callable($spec)) {
            return $spec;
        }

        if (is_string($spec)) {
            return $this->get($spec);
        }

        if (is_array($spec) && is_string($spec[0])) {
            $spec[0] = $this->get($spec[0]);
            return $spec;
        }

        throw new ContainerException(sprintf('Unable to resolve callable for %s', gettype($spec)));
    }

    protected function getFactory($factory) : callable
    {
        return $this->getCallable($factory);
    }

    protected function getExtension($extension) : callable
    {
        return $this->getCallable($extension);
    }
}
