<?php

declare(strict_types=1);

namespace Jnjxp\Container;

use Psr\Container\ContainerInterface;
use Interop\Container\ServiceProviderInterface;

class ContainerBuilder
{
    private $factories;

    private $aliases;

    private $instances;

    private $extensions;

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

    public function factory(string $name, $factory) : void
    {
        $this->factories[$name] = $factory;
    }

    public function factories(array $factories) : void
    {
        foreach ($factories as $name => $factory) {
            $this->factory($name, $factory);
        }
    }

    public function alias(string $name, string $implementation) : void
    {
        $this->aliases[$name] = $implementation;
    }

    public function aliases(array $aliases) : void
    {
        foreach ($aliases as $name => $alias) {
            $this->alias($name, $alias);
        }
    }

    public function instance(string $name, $instance) : void
    {
        $this->instances[$name] = $instance;
    }

    public function instances(array $instances) : void
    {
        foreach ($instances as $name => $instnace) {
            $this->instance($name, $instance);
        }
    }

    public function extension(string $name, $extension) : void
    {
        $this->extensions[$name][] = $extension;
    }

    public function extensions(array $specs) : void
    {
        foreach ($specs as $name => $extensions) {
            foreach ((array) $extensions as $extension) {
                $this->extension($name, $extension);
            }
        }
    }

    public function provider(ServiceProviderInterface $provider) : void
    {
        $this->factories($provider->getFactories());
        $this->extensions($provider->getExtensions());
    }

    public function providers(array $providers) : void
    {
        foreach ($providers as $provider) {
            $provider = is_string($provider) ? new $provider : $provider;
            $this->provider($provider);
        }
    }

    public function build() : ContainerInterface
    {
        return new Container(
            $this->factories,
            $this->aliases,
            $this->instances,
            $this->extensions
        );
    }
}
