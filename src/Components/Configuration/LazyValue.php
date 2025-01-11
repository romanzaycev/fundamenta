<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Configuration;

readonly class LazyValue
{
    public function __construct(
        private \Closure $callback,
    ) {}

    public function resolve(): mixed
    {
        return call_user_func($this->callback);
    }
}
