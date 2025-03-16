<?php

declare(strict_types=1);

namespace JnjxpTest\Container\Fake;

use Psr\Container\ContainerInterface as C;
use Jnjxp\Container\ServiceProvider\BaseServiceProvider;

class FakeFactoryProvider extends BaseServiceProvider
{
    public function getFactories(): array
    {
        return [
            FakeInterface::class => fn (C $cont) => new FakeService($cont->get(FakeDependency::class)),
        ];
    }
}
