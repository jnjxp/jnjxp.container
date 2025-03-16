<?php

declare(strict_types=1);

namespace JnjxpTest\Container\Fake;

class FakeUnionBuiltinParam
{
    public function __construct(public int|string $dependency = 1)
    {
    }
}
