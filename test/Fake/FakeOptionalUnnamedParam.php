<?php

declare(strict_types=1);

namespace JnjxpTest\Container\Fake;

class FakeOptionalUnnamedParam
{
    public function __construct(public $dependency = null)
    {
    }
}
