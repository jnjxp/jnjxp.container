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
    #[\Override]
    public function getFactories(): array
    {
        return [];
    }

    /**
     * getExtensions
     *
     * @return array<string, array<string|callable>>
     */
    #[\Override]
    public function getExtensions(): array
    {
        return [];
    }
}
