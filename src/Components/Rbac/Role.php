<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Rbac;

interface Role
{
    public function getCode(): string;
    public function getName(): string;
}
