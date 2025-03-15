<?php

declare(strict_types=1);

namespace JnjxpTest\Container\Fake;

class FakeNullableNoDefaultParam
{
    public function __construct(public ?FakeInterface $param)
    {
    }
}
