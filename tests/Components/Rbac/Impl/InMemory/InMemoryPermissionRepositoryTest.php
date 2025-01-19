<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Tests\Components\Rbac\Impl\InMemory;

use PHPUnit\Framework\TestCase;
use Romanzaycev\Fundamenta\Components\Rbac\Impl\InMemory\InMemoryPermissionRepository;
use Romanzaycev\Fundamenta\Components\Rbac\Models\Permission;
use Romanzaycev\Fundamenta\Components\Rbac\Models\Role;

class InMemoryPermissionRepositoryTest extends TestCase
{
    private function getInstance(): InMemoryPermissionRepository
    {
        return new InMemoryPermissionRepository(
            [
                new Permission("foo", "Foo perm"),
                new Permission("bar", "Bar perm"),
                new Permission("baz", "Baz perm"),
            ],
            [
                "baz_role" => ["foo"],
                "foobar_role" => ["foo", "bar"],
            ],
        );
    }

    public function testGetByRole(): void
    {
        $stubRole = new Role("foobar_role", "Foobar role");
        $this->assertEquals(
            [
                new Permission("foo", "Foo perm"),
                new Permission("bar", "Bar perm"),
            ],
            $this->getInstance()->getByRole($stubRole),
        );
    }

    public function testGetByRoleCode(): void
    {
        $this->assertEquals(
            [
                new Permission("foo", "Foo perm"),
                new Permission("bar", "Bar perm"),
            ],
            $this->getInstance()->getByRole("foobar_role"),
        );
    }

    public function testGetByUnknownRole(): void
    {
        $this->assertEquals(
            [],
            $this->getInstance()->getByRole("unk_role"),
        );
    }
}
