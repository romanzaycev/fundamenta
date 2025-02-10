<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Configuration;

readonly class LazyBoolEnv extends LazyValue
{
    public function __construct(string $variable, ?bool $default = null)
    {
        parent::__construct(static fn () => Env::getBool($variable, $default));
    }
}
