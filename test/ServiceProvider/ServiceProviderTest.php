<?php

declare(strict_types=1);

namespace JnjxpTest\Container\ServiceProvider;

use Jnjxp\Container\Container;
use Jnjxp\Container\ContainerBuilder;
use Jnjxp\Container\ContainerException;
use Jnjxp\Container\ServiceProvider\AbstractServiceProvider;
use Jnjxp\Container\ServiceProvider\AggregateServiceProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class ServiceProviderTest extends TestCase
{
    public function testServiceProvider()
    {
        $this->assertTrue(true);
    }
}
