<?php

declare(strict_types=1);

namespace Jnjxp\Container\Autowire;

use Exception;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use ReflectionType;

final class Autowire implements AutowireInterface
{
    /**
     * Create an instance of the specified class with dependencies resolved.
     *
     * @param class-string $className The name of the class to instantiate.
     * @param ContainerInterface|null $container An optional container for resolving dependencies.
     * @return object The instantiated class.
     * @throws AutowireException If the class cannot be instantiated.
     */
    #[\Override]
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

    /**
     * resolveDependencies
     *
     * @param  array<ReflectionParameter> $parameters
     * @param  ContainerInterface|null $container
     * @return array<mixed>
     */
    private function resolveDependencies(array $parameters, ?ContainerInterface $container): array
    {
        return array_map(fn(ReflectionParameter $parameter): mixed
            => $this->resolveParameter($parameter, $container), $parameters);
    }

    private function resolveParameter(ReflectionParameter $parameter, ?ContainerInterface $container): mixed
    {
        $type = $parameter->getType();

        if ($type instanceof ReflectionUnionType) {
            return $this->resolveUnionTypeParameter($parameter, $type->getTypes(), $container);
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
            $parameter->getDeclaringClass()?->getName() ?? 'class'
        );
    }

    /**
     * resolveUnionTypeParameter
     *
     * @param  ReflectionParameter $parameter
     * @param  array<ReflectionType> $types
     * @param  ContainerInterface|null $container
     * @throws AutowireException
     * @return mixed
     */
    private function resolveUnionTypeParameter(
        ReflectionParameter $parameter,
        array $types,
        ?ContainerInterface $container
    ): mixed {
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
            $parameter->getDeclaringClass()?->getName() ?? 'class'
        );
    }

    /**
     * resolveClassDependency
     *
     * @param  ReflectionParameter $parameter
     * @param  class-string $dependencyClass
     * @param  ContainerInterface|null $container
     * @return mixed
     */
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
            $parameter->getDeclaringClass()?->getName() ?? 'class'
        );
    }
}
