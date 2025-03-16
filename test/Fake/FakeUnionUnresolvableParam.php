<?php

declare(strict_types=1);

namespace JnjxpTest\Container\Fake;

use Iterator;

class FakeUnionUnresolvableParam
{
    public function __construct(public FakeInterface|Iterator $dependency)
    {
    }
}
