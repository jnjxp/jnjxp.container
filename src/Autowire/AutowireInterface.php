<?php

declare(strict_types=1);

namespace Jnjxp\Container\Autowire;

use Psr\Container\ContainerInterface;

interface AutowireInterface
{
    /**
     * Create an instance of the specified class with dependencies resolved.
     *
     * @param class-string $className The name of the class to instantiate.
     * @param ContainerInterface|null $container An optional container for resolving dependencies.
     * @return object The instantiated class.
     * @throws AutowireException If the class cannot be instantiated.
     */
    public function create(string $className, ?ContainerInterface $container = null): object;
}
