<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Rbac\Models;

readonly class Permission implements \Romanzaycev\Fundamenta\Components\Rbac\Permission
{
    public function __construct(
        private string $code,
        private string $name,
    ) {}

    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
