<?php

declare(strict_types=1);

namespace Jnjxp\Container\Autowire;

use Exception;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

class Autowire implements AutowireInterface
{
    /**
     * Create an instance of the specified class with dependencies resolved.
     *
     * @param string $className The name of the class to instantiate.
     * @param ContainerInterface|null $container An optional container for resolving dependencies.
     * @return object The instantiated class.
     * @throws AutowireException If the class cannot be instantiated.
     */
    public function create(string $className, ?ContainerInterface $container = null): object
    {
        $reflectionClass = new ReflectionClass($className);
        $this->assertClassIsInstantiable($reflectionClass);

        $constructor = $reflectionClass->getConstructor();
        if ($constructor === null) {
            return $reflectionClass->newInstance();
        }

        $parameters = $constructor->getParameters();
        $dependencies = $this->resolveDependencies($parameters, $container);

        return $reflectionClass->newInstanceArgs($dependencies);
    }

    private function assertClassIsInstantiable(ReflectionClass $reflectionClass): void
    {
        if (!$reflectionClass->isInstantiable()) {
            throw AutowireException::cannotInstantiate($reflectionClass->getName());
        }
    }

    private function resolveDependencies(array $parameters, ?ContainerInterface $container): array
    {
        return array_map(fn($parameter) => $this->resolveParameter($parameter, $container), $parameters);
    }

    private function resolveParameter(ReflectionParameter $parameter, ?ContainerInterface $container): mixed
    {
        $type = $parameter->getType();

        if ($type instanceof ReflectionUnionType) {
            return $this->resolveUnionTypeParameter($parameter, $container);
        }

        if ($type instanceof ReflectionNamedType) {
            if (!$type->isBuiltin()) {
                return $this->resolveClassDependency($parameter, $type->getName(), $container);
            }
            return $this->resolveBuiltinParameter($parameter);
        }

        if ($parameter->isOptional()) {
            return $this->resolveOptionalParameter($parameter);
        }

        throw AutowireException::cannotResolveParamForClass(
            $parameter->getName(),
            $parameter->getDeclaringClass()->getName()
        );
    }

    private function resolveUnionTypeParameter(ReflectionParameter $parameter, ?ContainerInterface $container): mixed
    {
        $types = $parameter->getType()->getTypes();
        foreach ($types as $type) {
            if ($type instanceof ReflectionNamedType) {
                if (!$type->isBuiltin()) {
                    try {
                        return $this->resolveClassDependency($parameter, $type->getName(), $container);
                    } catch (AutowireException $e) {
                        // Continue to the next type
                    }
                } else {
                    return $this->resolveBuiltinParameter($parameter);
                }
            }
        }

        throw AutowireException::cannotResolveParamForClass(
            $parameter->getName(),
            $parameter->getDeclaringClass()->getName()
        );
    }

    private function resolveClassDependency(
        ReflectionParameter $parameter,
        string $dependencyClass,
        ?ContainerInterface $container
    ): mixed {
        if ($container && $container->has($dependencyClass)) {
            return $container->get($dependencyClass);
        }

        if ($parameter->isOptional() || $parameter->allowsNull()) {
            return $this->resolveOptionalParameter($parameter);
        }

        return $this->create($dependencyClass, $container);
    }

    private function resolveOptionalParameter(ReflectionParameter $parameter): mixed
    {
        return $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;
    }

    private function resolveBuiltinParameter(ReflectionParameter $parameter): mixed
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if ($parameter->allowsNull()) {
            return null;
        }

        throw AutowireException::cannotResolveParamForClass(
            $parameter->getName(),
            $parameter->getDeclaringClass()->getName()
        );
    }
}
