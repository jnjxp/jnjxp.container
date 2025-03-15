<?php

declare(strict_types=1);

namespace Jnjxp\Container\Autowire;

use Jnjxp\Container\ContainerException;

class AutowireException extends ContainerException
{
    public static function cannotInstantiate(string $name): self
    {
        return new self("Class {$name} is not instantiable.");
    }

    public static function cannotResolveParamForClass(string $parameter, string $class): self
    {
        return new self("Cannot resolve dependency for parameter '{$parameter}' in class '{$class}'.");
    }
}
