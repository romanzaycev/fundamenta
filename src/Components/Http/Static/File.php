<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Http\Static;

readonly class File implements InternalStaticFileInterface
{
    public function __construct(
        private string $public,
        private string $real,
    ) {}

    public function getPublicFile(): string
    {
        return $this->public;
    }

    public function getRealFile(): string
    {
        return $this->real;
    }
}
