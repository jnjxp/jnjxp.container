<?php

declare(strict_types=1);

namespace JnjxpTest\Container\Fake;

use Psr\Container\ContainerInterface as C;
use Jnjxp\Container\ServiceProvider\BaseServiceProvider;

class FakeExtensionProvider extends BaseServiceProvider
{
    public function getExtensions(): array
    {
        return [
            FakeInterface::class => [
                fn (C $cont, FakeService $service) => new FakeDecorator($service, $cont->get(FakeDependency::class)),
            ],
        ];
    }
}
