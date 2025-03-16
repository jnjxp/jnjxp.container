<?php

declare(strict_types=1);

namespace JnjxpTest\Container;

use JnjxpTest\Container\Fake\FakeDefaultBuiltinParam;
use JnjxpTest\Container\Fake\FakeDependency;
use JnjxpTest\Container\Fake\FakeNonInstantiableClass;
use JnjxpTest\Container\Fake\FakeNullableBuiltinParam;
use JnjxpTest\Container\Fake\FakeNullableDefaultParam;
use JnjxpTest\Container\Fake\FakeNullableNoDefaultParam;
use JnjxpTest\Container\Fake\FakeOptionalUnnamedParam;
use JnjxpTest\Container\Fake\FakeRequiredUnnamedParam;
use JnjxpTest\Container\Fake\FakeService;
use JnjxpTest\Container\Fake\FakeUnionBuiltinParam;
use JnjxpTest\Container\Fake\FakeUnionOptionalParam;
use JnjxpTest\Container\Fake\FakeUnionParam;
use JnjxpTest\Container\Fake\FakeUnionUnresolvableParam;
use JnjxpTest\Container\Fake\FakeUnresolvableTypedParam;
use Jnjxp\Container\Autowire\Autowire;
use Jnjxp\Container\ContainerException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class AutowireTest extends TestCase
{
    public function testCreateInstanceWithoutContainer()
    {
        $autowire = new Autowire();
        $service = $autowire->create(FakeService::class);

        $this->assertInstanceOf(FakeService::class, $service);
        $this->assertInstanceOf(FakeDependency::class, $service->dependency);
    }

    public function testCreateInstanceWithContainer()
    {
        $container = $this->createMock(ContainerInterface::class);
        $dependency = new FakeDependency();

        $container->method('has')
            ->willReturnMap([
                [FakeDependency::class, true],
            ]);

        $container->method('get')
            ->willReturnMap([
                [FakeDependency::class, $dependency],
            ]);

        $autowire = new Autowire();
        $service = $autowire->create(FakeService::class, $container);

        $this->assertInstanceOf(FakeService::class, $service);
        $this->assertSame($dependency, $service->dependency);
    }

    public function testCreateInstanceWithOptionalParameter()
    {
        $autowire = new Autowire();
        $service = $autowire->create(FakeDefaultBuiltinParam::class);

        $this->assertEquals(FakeDefaultBuiltinParam::DEFAULT_PARAM, $service->param);
    }

    public function testCreateInstanceThrowsExceptionForNonInstantiableClass()
    {
        $this->expectException(ContainerException::class);

        $autowire = new Autowire();
        $autowire->create(FakeNonInstantiableClass::class);
    }

    public function testExceptionOnUnresolvableParam()
    {
        $this->expectException(ContainerException::class);

        $autowire = new Autowire();
        $autowire->create(FakeUnresolvableTypedParam::class);
    }

    public function testOptionalUnnamedParam()
    {
        $autowire = new Autowire();
        $service = $autowire->create(FakeOptionalUnnamedParam::class);
        $this->assertInstanceOf(FakeOptionalUnnamedParam::class, $service);
    }

    public function testExceptionOnRequiredUnnamedParam()
    {
        $this->expectException(ContainerException::class);
        $autowire = new Autowire();
        $autowire->create(FakeRequiredUnnamedParam::class);
    }

    public function testNullableNoDefaultParam()
    {
        $autowire = new Autowire();
        $service = $autowire->create(FakeNullableNoDefaultParam::class);
        $this->assertNull($service->param);
    }

    public function testNullableDefaultParam()
    {
        $autowire = new Autowire();
        $service = $autowire->create(FakeNullableDefaultParam::class);
        $this->assertInstanceOf(FakeService::class, $service->param);
    }

    public function testNullableBuiltinParam()
    {
        $autowire = new Autowire();
        $service = $autowire->create(FakeNullableBuiltinParam::class);
        $this->assertInstanceOf(FakeNullableBuiltinParam::class, $service);
    }

    public function testUnionParam()
    {
        $autowire = new Autowire();
        $service = $autowire->create(FakeUnionParam::class);
        $this->assertInstanceOf(FakeUnionParam::class, $service);
    }

    public function testUnionBuiltinParam()
    {
        $autowire = new Autowire();
        $service = $autowire->create(FakeUnionBuiltinParam::class);
        $this->assertInstanceOf(FakeUnionBuiltinParam::class, $service);
    }

    public function testUnionUnresolvableParam()
    {
        $this->expectException(ContainerException::class);
        $autowire = new Autowire();
        $service = $autowire->create(FakeUnionUnresolvableParam::class);
        $this->assertInstanceOf(FakeUnionUnresolvableParam::class, $service);
    }

    public function testUnionOptionalParam()
    {
        $autowire = new Autowire();
        $service = $autowire->create(FakeUnionOptionalParam::class);
        $this->assertInstanceOf(FakeUnionOptionalParam::class, $service);
    }
}
