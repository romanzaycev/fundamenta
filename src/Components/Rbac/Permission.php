<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Rbac;

interface Permission
{
    public function getCode(): string;
    public function getName(): string;
}
