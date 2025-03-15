<?php

declare(strict_types=1);

namespace Jnjxp\Container;

use Jnjxp\Container\Autowire\Autowire;
use Jnjxp\Container\Autowire\AutowireInterface;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * @param array<string, mixed> $factories
     * @param array<string, string> $aliases
     * @param array<string, mixed> $instances
     * @param array<string, mixed[]> $extensions
     * @param ?AutowireInterface $autowire
     */
    public function __construct(
        protected array $factories = [],
        protected array $aliases = [],
        protected array $instances = [],
        protected array $extensions = [],
        protected ?AutowireInterface $autowire = null,
    ) {
    }

    #[\Override]
    public function get(string $identity): mixed
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

        if (isset($this->autowire)) {
            return $this->autowire->create($identity);
        }

        return $this->fromNew($identity);
    }

    #[\Override]
    public function has(string $identity): bool
    {
        return isset($this->instances[$identity])
            || isset($this->factories[$identity])
            || isset($this->aliases[$identity]);
    }

    protected function fromAlias(string $identity): mixed
    {
        $implementation = $this->aliases[$identity];
        $instance = $this->get($implementation);
        $this->instances[$identity] = $instance;
        return $instance;
    }

    protected function fromFactory(string $identity): mixed
    {
        $factory  = $this->getFactory($this->factories[$identity]);
        $instance = $factory($this, $identity);
        $instance = $this->extend($identity, $instance);
        $this->instances[$identity] = $instance;
        return $instance;
    }

    protected function fromNew(string $identity): mixed
    {
        $instance = new $identity();
        $instance = $this->extend($identity, $instance);
        $this->instances[$identity] = $instance;
        return $instance;
    }

    protected function extend(string $identity, mixed $instance): mixed
    {
        if (isset($this->extensions[$identity])) {
            foreach ($this->extensions[$identity] as $extension) {
                $extension = $this->getExtension($extension);
                $instance = $extension($this, $instance);
            }
        }
        return $instance;
    }

    protected function getCallable(mixed $spec): callable
    {
        if (is_callable($spec)) {
            return $spec;
        }

        if (is_string($spec)) {
            return $this->get($spec);
        }

        if (is_array($spec) && is_string($spec[0])) {
            $spec[0] = $this->get($spec[0]);
            if (is_callable($spec)) {
                return $spec;
            }
        }

        throw new ContainerException(sprintf('Unable to resolve callable for %s', gettype($spec)));
    }

    protected function getFactory(mixed $factory): callable
    {
        return $this->getCallable($factory);
    }

    protected function getExtension(mixed $extension): callable
    {
        return $this->getCallable($extension);
    }
}
