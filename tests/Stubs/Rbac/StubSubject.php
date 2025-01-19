<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Tests\Stubs\Rbac;

use Romanzaycev\Fundamenta\Components\Rbac\Subject;

readonly class StubSubject implements Subject
{
    public function __construct(
        private string $id,
    ) {}

    public function getSubjectId(): string
    {
        return $this->id;
    }
}
