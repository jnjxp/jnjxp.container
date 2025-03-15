<?php

declare(strict_types=1);

namespace Jnjxp\Container;

use Interop\Container\ServiceProviderInterface;
use Jnjxp\Container\Autowire\Autowire;
use Jnjxp\Container\Autowire\AutowireInterface;
use Psr\Container\ContainerInterface;

/**
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 */
class ContainerBuilder
{
    /**
     * @param array<string, mixed> $factories
     * @param array<string, string> $aliases
     * @param array<string, mixed> $instances
     * @param array<string, mixed[]> $extensions
     * @param ?AutowireInterface $autowire
     */
    public function __construct(
        private array $factories = [],
        private array $aliases = [],
        private array $instances = [],
        private array $extensions = [],
        private ?AutowireInterface $autowire = null,
    ) {
    }

    public function factory(string $name, mixed $factory): self
    {
        $this->factories[$name] = $factory;
        return $this;
    }

    /**
     * @param array<string, mixed> $factories
     */
    public function factories(array $factories): self
    {
        foreach ($factories as $name => $factory) {
            $this->factory($name, $factory);
        }
        return $this;
    }

    public function alias(string $name, string $implementation): self
    {
        $this->aliases[$name] = $implementation;
        return $this;
    }

    /**
     * @param array<string, string> $aliases
     */
    public function aliases(array $aliases): self
    {
        foreach ($aliases as $name => $alias) {
            $this->alias($name, $alias);
        }
        return $this;
    }

    public function instance(string $name, mixed $instance): self
    {
        $this->instances[$name] = $instance;
        return $this;
    }

    /**
     * @param array<string, mixed> $instances
     */
    public function instances(array $instances): self
    {
        foreach ($instances as $name => $instance) {
            $this->instance($name, $instance);
        }
        return $this;
    }

    public function extension(string $name, mixed $extension): self
    {
        $this->extensions[$name][] = $extension;
        return $this;
    }

    /**
     * @param array<string, mixed>[] $specs
     */
    public function extensions(array $specs): self
    {
        foreach ($specs as $name => $extensions) {
            foreach ((array) $extensions as $extension) {
                $this->extension($name, $extension);
            }
        }
        return $this;
    }

    public function provider(string|ServiceProviderInterface $provider): self
    {
        $provider = is_string($provider) ? new $provider() : $provider;

        if (! $provider instanceof ServiceProviderInterface) {
            throw new ContainerException(sprintf(
                "%s is not instance of ServiceProviderInterface",
                get_class($provider)
            ));
        }

        $this->factories($provider->getFactories());
        $this->extensions($provider->getExtensions());
        return $this;
    }

    /**
     * @param ServiceProviderInterface[]|string[] $providers
     */
    public function providers(array $providers): self
    {
        foreach ($providers as $provider) {
            $this->provider($provider);
        }
        return $this;
    }

    public function autowire(null|bool|string|AutowireInterface $autowire): self
    {
        if (! $autowire) {
            $this->autowire = null;
            return $this;
        }

        if (true === $autowire) {
            $this->autowire = new Autowire();
            return $this;
        }

        $this->autowire = new $autowire();
        return $this;
    }

    public function build(): ContainerInterface
    {
        return new Container(
            factories: $this->factories,
            aliases: $this->aliases,
            instances: $this->instances,
            extensions: $this->extensions,
            autowire: $this->autowire,
        );
    }
}
