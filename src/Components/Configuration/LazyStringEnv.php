<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Configuration;

readonly class LazyStringEnv extends LazyValue
{
    public function __construct(string $variable, ?string $default = null)
    {
        parent::__construct(static fn () => Env::getString($variable, $default));
    }
}
