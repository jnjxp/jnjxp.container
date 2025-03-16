<?php

declare(strict_types=1);

namespace JnjxpTest\Container\Fake;

class FakeDecorator implements FakeInterface
{
    public function __construct(public FakeInterface $inner, public FakeDependency $dependency)
    {
    }

    public function doSomething(): FakeDependency
    {
        return $this->inner->dependency;
    }
}
