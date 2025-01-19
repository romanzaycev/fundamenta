<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Tests\Stubs\Rbac;

use Romanzaycev\Fundamenta\Components\Auth\User;

readonly class StubUser implements User
{
    public function __construct(
        private string $id,
    ) {}

    public function getId(): string
    {
        return $this->id;
    }
}
