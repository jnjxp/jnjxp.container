<?php

declare(strict_types=1);

namespace JnjxpTest\Container\Fake;

class FakeNullableDefaultParam
{
    public function __construct(public ?FakeInterface $param = new FakeService(new FakeDependency()))
    {
    }
}
