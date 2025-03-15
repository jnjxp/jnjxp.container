<?php

declare(strict_types=1);

namespace JnjxpTest\Container\Fake;

class FakeRequiredUnnamedParam
{
    public function __construct(public $dependency)
    {
    }
}
