<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Monolog;

use Monolog\Handler\HandlerInterface;

interface HandlerProvider
{
    public function get(): ?HandlerInterface;
}
