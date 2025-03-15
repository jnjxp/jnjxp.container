<?php

declare(strict_types=1);

namespace JnjxpTest\Container\Fake;

use Iterator;
use ArrayAccess;

class FakeUnionOptionalParam
{
    public function __construct(public null|int|FakeInterface $dependency)
    {
    }
}
