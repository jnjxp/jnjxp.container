<?php

declare(strict_types=1);

namespace JnjxpTest\Container\Fake;

interface FakeInterface
{
    public function doSomething(): FakeDependency;
}
