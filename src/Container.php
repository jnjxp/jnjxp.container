<?php

declare(strict_types=1);

namespace Jnjxp\Container;

use Jnjxp\Container\Autowire\Autowire;
use Jnjxp\Container\Autowire\AutowireInterface;
use Psr\Container\ContainerInterface;

final class Container implements ContainerInterface
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

    /**
     * @SuppressWarnings("PHPMD.ShortVariable")
     */
    #[\Override]
    public function get(string $id): mixed
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (isset($this->aliases[$id])) {
            return $this->fromAlias($id);
        }

        if (isset($this->factories[$id])) {
            return $this->fromFactory($id);
        }

        if (isset($this->autowire)) {
            /** @psalm-suppress ArgumentTypeCoercion */
            return $this->autowire->create($id);
        }

        return $this->fromNew($id);
    }

    /**
     * @SuppressWarnings("PHPMD.ShortVariable")
     */
    #[\Override]
    public function has(string $id): bool
    {
        return isset($this->instances[$id])
            || isset($this->factories[$id])
            || isset($this->aliases[$id]);
    }

    protected function fromAlias(string $identity): mixed
    {
        $implementation = $this->aliases[$identity];
        /** @var mixed */
        $instance = $this->get($implementation);
        $this->instances[$identity] = $instance;
        return $instance;
    }

    protected function fromFactory(string $identity): mixed
    {
        $factory  = $this->getFactory($this->factories[$identity]);
        /** @var mixed */
        $instance = $factory($this, $identity);
        /** @var mixed */
        $instance = $this->extend($identity, $instance);
        $this->instances[$identity] = $instance;
        return $instance;
    }

    protected function fromNew(string $identity): mixed
    {
        try {
            $instance = new $identity();
        } catch (\Error $error) {
            throw new NotFoundException(message: "$identity not found", previous: $error);
        }
        /** @var mixed */
        $instance = $this->extend($identity, $instance);
        $this->instances[$identity] = $instance;
        return $instance;
    }

    protected function extend(string $identity, mixed $instance): mixed
    {
        if (isset($this->extensions[$identity])) {
            /** @var callable|string|callable-array $extension */
            foreach ($this->extensions[$identity] as $extension) {
                $extension = $this->getExtension($extension);
                /** @var mixed */
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
            /** @var callable */
            return $this->get($spec);
        }

        if (is_array($spec) && is_string($spec[0])) {
            /** @var string|object $object */
            $object = $this->get($spec[0]);
            $spec[0] = $object;
            /** @var callable-array $spec */
            return $spec;
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
