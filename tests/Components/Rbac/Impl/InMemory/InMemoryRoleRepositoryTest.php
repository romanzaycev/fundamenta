<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Tests\Components\Rbac\Impl\InMemory;

use PHPUnit\Framework\TestCase;
use Romanzaycev\Fundamenta\Components\Rbac\Impl\InMemory\InMemoryRoleRepository;
use Romanzaycev\Fundamenta\Components\Rbac\Models\Role;
use Romanzaycev\Fundamenta\Tests\Stubs\Rbac\StubSubject;

class InMemoryRoleRepositoryTest extends TestCase
{
    private function getInstance(): InMemoryRoleRepository
    {
        return new InMemoryRoleRepository(
            [
                new Role("foo", "Foo role"),
                new Role("bar", "Bar role"),
                new Role("baz", "Baz role"),
            ],
            [
                "subj1" => ["foo", "baz"],
            ]
        );
    }

    public function testGetRoles(): void
    {
        $this
            ->assertEquals(
                [
                    new Role("foo", "Foo role"),
                    new Role("baz", "Baz role"),
                ],
                $this->getInstance()->getBySubject("subj1"),
                "Getting by id",
            );
        $this
            ->assertEquals(
                [
                    new Role("foo", "Foo role"),
                    new Role("baz", "Baz role"),
                ],
                $this->getInstance()->getBySubject(new StubSubject("subj1")),
                "Getting by instance",
            );
    }
}
