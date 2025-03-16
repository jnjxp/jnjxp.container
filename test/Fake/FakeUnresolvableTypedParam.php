<?php

declare(strict_types=1);

namespace JnjxpTest\Container\Fake;

class FakeUnresolvableTypedParam
{
    public function __construct(public int $param)
    {
    }
}
