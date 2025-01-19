<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Rbac;

interface Subject
{
    public function getSubjectId(): string;
}
