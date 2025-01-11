<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Tests\Components\Eav\Impl\Pgsql;

use PHPUnit\Framework\TestCase;
use Romanzaycev\Fundamenta\Components\Eav\Impl\Pgsql\QueryContext;
use Romanzaycev\Fundamenta\Components\Eav\Operator;

class QueryContextTest extends TestCase
{
    public function testPlaceholder(): void
    {
        $instance = new QueryContext();
        $result = $instance->bindReturnPlaceholder(
            "foo",
            Operator::EQ,
            "bar",
        );
        $this->assertEquals(
            ":foo",
            $result,
        );
    }

    public function testInPlaceholder(): void
    {
        $instance = new QueryContext();
        $result = $instance->bindReturnPlaceholder(
            "foo",
            Operator::IN,
            ["bar", "baz"],
        );
        $this->assertEquals(
            ":foo_0, :foo_1",
            $result,
        );
    }

    public function testBind(): void
    {
        $instance = new QueryContext();
        $_ = $instance->bindReturnPlaceholder(
            "foo",
            Operator::EQ,
            "bar",
        );
        $this->assertEquals(
            ["foo" => "bar"],
            $instance->getBindings(),
        );
    }

    public function testBindIn(): void
    {
        $instance = new QueryContext();
        $_ = $instance->bindReturnPlaceholder(
            "foo",
            Operator::IN,
            ["bar", "baz"],
        );
        $this->assertEquals(
            ["foo_0" => "bar", "foo_1" => "baz"],
            $instance->getBindings(),
        );
    }
}
