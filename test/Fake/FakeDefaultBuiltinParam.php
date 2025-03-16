<?php

declare(strict_types=1);

namespace JnjxpTest\Container\Fake;

class FakeDefaultBuiltinParam
{
    public const DEFAULT_PARAM = 'default';

    public function __construct(public string $param = self::DEFAULT_PARAM)
    {
    }
}
