<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Configuration;

readonly class LazyIntEnv extends LazyValue
{
    public function __construct(string $variable, ?int $default = null)
    {
        parent::__construct(static fn () => Env::getInt($variable, $default));
    }
}
