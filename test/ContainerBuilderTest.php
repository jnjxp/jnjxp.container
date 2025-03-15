<?php

declare(strict_types=1);

namespace JnjxpTest\Container;

use Error;
use JnjxpTest\Container\Fake\FakeAutowire;
use JnjxpTest\Container\Fake\FakeDecorator;
use JnjxpTest\Container\Fake\FakeDependency;
use JnjxpTest\Container\Fake\FakeExtensionProvider;
use JnjxpTest\Container\Fake\FakeFactoryProvider;
use JnjxpTest\Container\Fake\FakeInterface;
use JnjxpTest\Container\Fake\FakeService;
use Jnjxp\Container\Container;
use Jnjxp\Container\ContainerBuilder;
use Jnjxp\Container\ContainerException;
use Jnjxp\Container\ServiceProvider\AggregateServiceProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class ContainerBuilderTest extends TestCase
{
    public function testContainerFactories()
    {
        $name = 'FOO';
        $service = (object) ['SERVICE'];
        $factory = fn() => $service;

        $factories = [ $name => $factory ];

        $container = new ContainerBuilder()->factories($factories)->build();

        $this->assertFalse($container->has('BAR'));
        $this->assertTrue($container->has($name));
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

        $container = new ContainerBuilder()
            ->factories($factories)
            ->aliases($aliases)
            ->build();

        $this->assertFalse($container->has('BAR'));
        $this->assertTrue($container->has($name));
        $this->assertTrue($container->has($alias));
        $this->assertSame($service, $container->get($name));
        $this->assertSame($service, $container->get($alias));

        // X2
        $this->assertSame($service, $container->get($name));
        $this->assertSame($service, $container->get($name));
    }

    public function testInstances()
    {
        $name = 'FOO';
        $service = (object) ['SERVICE'];

        $instances = [ $name => $service ];

        $container = new ContainerBuilder()
            ->instances($instances)
            ->build();

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


        $container = new ContainerBuilder()
            ->factories($factories)
            ->extensions($extensions)
            ->build();

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

    public function testFactoryProviders()
    {
        $container = new ContainerBuilder()->providers([FakeFactoryProvider::class])->build();

        $service = $container->get(FakeInterface::class);
        $this->assertInstanceOf(FakeService::class, $service);
        $this->assertInstanceOf(FakeDependency::class, $service->dependency);
    }

    public function testExtensionProviders()
    {
        $container = new ContainerBuilder()
            ->factories([FakeInterface::class => fn() => new FakeService(new FakeDependency())])
            ->providers([FakeExtensionProvider::class])
            ->build();

        $service = $container->get(FakeInterface::class);
        $this->assertInstanceOf(FakeDecorator::class, $service);
        $this->assertInstanceOf(FakeDependency::class, $service->doSomething());
    }

    public function testAggregateProviders()
    {
        $container = new ContainerBuilder()
            ->provider(new AggregateServiceProvider(
                FakeFactoryProvider::class,
                FakeExtensionProvider::class
            ))->build();

        $service = $container->get(FakeInterface::class);
        $this->assertInstanceOf(FakeDecorator::class, $service);
        $this->assertInstanceOf(FakeDependency::class, $service->doSomething());
    }

    public function testBadProviders()
    {
        $this->expectException(ContainerException::class);
        $container = new ContainerBuilder()->providers([FakeDependency::class])->build();
    }

    public function testAutowire()
    {
        $container = new ContainerBuilder()->autowire(true)->build();
        $service = $container->get(FakeService::class);
        $this->assertInstanceOf(FakeService::class, $container->get(FakeService::class));
    }

    public function testDisableAutowire()
    {
        $container = new ContainerBuilder()->autowire(true)->autowire(false)->build();
        $this->expectException(Error::class);
        $service = $container->get(FakeService::class);
    }

    public function testCustomAutowire()
    {
        $container = new ContainerBuilder()->autowire(FakeAutowire::class)->build();
        $this->assertInstanceOf(\stdClass::class, $container->get(FakeService::class));
    }
}
