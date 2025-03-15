<?php

declare(strict_types=1);

namespace JnjxpTest\Container;

use Jnjxp\Container\Container;
use Jnjxp\Container\ContainerException;
use Jnjxp\Container\NotFoundException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class ContainerTest extends TestCase
{
    public function testContainerNotFound()
    {
        $this->expectException(NotFoundException::class);
        $container = new Container();
        $container->get('FOO');
    }

    public function testContainerFactories()
    {
        $name = 'FOO';
        $service = (object) ['SERVICE'];
        $factory = fn() => $service;

        $factories = [ $name => $factory ];

        $container = new Container(factories: $factories);

        $this->assertFalse($container->has('BAR'));
        $this->assertTrue($container->has($name));
        $this->assertSame($service, $container->get($name));
        $this->assertSame($service, $container->get($name));
    }

    public function testSameInstance()
    {
        $name = 'FOO';
        $service = (object) ['SERVICE'];
        $factory = fn() => $service;

        $factories = [ $name => $factory ];

        $container = new Container(factories: $factories);

        // X2
        $this->assertSame($service, $container->get($name));
        $this->assertSame($service, $container->get($name));
    }

    public function testSameAlias()
    {
        $name = 'FOO';
        $alias = 'foo';
        $service = (object) ['SERVICE'];
        $factory = fn() => $service;

        $factories = [ $name => $factory ];
        $aliases = [ $alias => $name ];

        $container = new Container(
            factories: $factories,
            aliases: $aliases,
        );

        $this->assertFalse($container->has('BAR'));
        $this->assertTrue($container->has($name));
        $this->assertTrue($container->has($alias));
        $this->assertSame($service, $container->get($name));
        $this->assertSame($service, $container->get($alias));
    }

    public function testFromNew()
    {
        $container = new Container();
        $this->assertFalse($container->has(Container::class));
        $this->assertInstanceOf(Container::class, $container->get(Container::class));
    }

    public function testInstances()
    {
        $name = 'FOO';
        $service = (object) ['SERVICE'];

        $instances = [ $name => $service ];

        $container = new Container(instances: $instances);

        $this->assertSame($service, $container->get($name));
    }

    public function testExtend()
    {
        $service_name = 'FOO';
        $service = (object) ['name' => 'SERVICE'];
        $service_factory = fn() => $service;

        $extra_name = 'FOO_EXTRA';
        $extra = (object) ['name' => 'extra'];
        $extra_factory = fn() => $extra;

        $extension = fn(ContainerInterface $container, object $service): object => (object) [
            'service' => $service,
            'extra'   => $container->get($extra_name)
        ];


        $factories = [
            $service_name => $service_factory,
            $extra_name   => $extra_factory,
        ];

        $extensions = [
            $service_name => [ $extension ]
        ];

        $container = new Container(
            factories: $factories,
            extensions: $extensions,
        );

        $this->assertSame($service, $container->get($service_name)->service);
        $this->assertSame($extra, $container->get($service_name)->extra);
    }

    public function testNamedCallables()
    {
        $service_name = 'FOO';
        $service = (object) ['name' => 'SERVICE'];

        $service_factory_name = 'FOO_FACTORY';
        $service_factory = fn() => $service;

        $factories = [ $service_name => $service_factory_name ];
        $instances = [ $service_factory_name => $service_factory ];

        $container = new Container(
            factories: $factories,
            instances: $instances,
        );

        $this->assertSame($service, $container->get($service_name));
    }

    public function testCallablesArray()
    {
        $service_name = 'FOO';
        $service = (object) ['name' => 'SERVICE'];

        $service_factory_name = 'FOO_FACTORY';
        $service_factory_spec = [$service_factory_name, 'newService'];
        $service_factory = new class ($service) {
            public function __construct(protected object $service)
            {
            }
            public function newService(): object
            {
                return $this->service;
            }
        };

        $factories = [ $service_name => $service_factory_spec ];
        $instances = [ $service_factory_name => $service_factory ];

        $container = new Container(
            factories: $factories,
            instances: $instances,
        );

        $this->assertSame($service, $container->get($service_name));
    }

    public function testInvalidCallable()
    {
        $service_name = 'FOO';
        $bad_service_factory = [1,1];

        $factories = [ $service_name => $bad_service_factory ];

        $container = new Container(factories: $factories);

        $this->expectException(ContainerException::class);
        $container->get($service_name);
    }
}
