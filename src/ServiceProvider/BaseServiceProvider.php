<?php

declare(strict_types=1);

namespace Jnjxp\Container\ServiceProvider;

use Interop\Container\ServiceProviderInterface;

class BaseServiceProvider implements ServiceProviderInterface
{
    /**
     * getFactories
     *
     * @return array<string, string|callable>
     */
    public function getFactories(): array
    {
        return [];
    }

    /**
     * getExtensions
     *
     * @return array<string, array<string|callable>>
     */
    public function getExtensions(): array
    {
        return [];
    }
}
