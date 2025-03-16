<?php

declare(strict_types=1);

namespace JnjxpTest\Container\Fake;

class FakeUnionParam
{
    public function __construct(public FakeInterface|FakeService $dependency)
    {
    }
}
