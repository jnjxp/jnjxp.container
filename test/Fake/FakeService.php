<?php

declare(strict_types=1);

namespace JnjxpTest\Container\Fake;

class FakeService implements FakeInterface
{
    public function __construct(public FakeDependency $dependency)
    {
    }

    public function doSomething(): FakeDependency
    {
        return $this->dependency;
    }
}
