<?php

declare(strict_types=1);

namespace Jnjxp\Container;

use Psr\Container\NotFoundExceptionInterface;

final class NotFoundException extends \Exception implements NotFoundExceptionInterface
{
}
