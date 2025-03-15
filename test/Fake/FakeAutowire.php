<?php

declare(strict_types=1);

namespace JnjxpTest\Container\Fake;

use Jnjxp\Container\Autowire\AutowireInterface;
use Psr\Container\ContainerInterface;

class FakeAutowire implements AutowireInterface
{
    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function create(string $class, ?ContainerInterface $container = null): object
    {
        return (object) [];
    }
}
