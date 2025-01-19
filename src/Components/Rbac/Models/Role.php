<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Rbac\Models;

readonly class Role implements \Romanzaycev\Fundamenta\Components\Rbac\Role
{
    public function __construct(
        private string $code,
        private string $name,
    ) {}

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
