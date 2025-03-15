<?php

declare(strict_types=1);

namespace Jnjxp\Container\Autowire;

use Psr\Container\ContainerInterface;

interface AutowireInterface
{
    public function create(string $className, ?ContainerInterface $container = null): object;
}
