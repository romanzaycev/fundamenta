<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Http\Static;

readonly class Directory implements InternalStaticDirectoryInterface
{
    public function __construct(
        private string $public,
        private string $real,
    ) {}

    public function getPublicDirectory(): string
    {
        return $this->public;
    }

    public function getRealDirectory(): string
    {
        return $this->real;
    }
}
