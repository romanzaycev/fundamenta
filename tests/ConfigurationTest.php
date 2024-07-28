<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Tests;

use PHPUnit\Framework\TestCase;
use Romanzaycev\Fundamenta\Configuration;

class ConfigurationTest extends TestCase
{
    private const STUB = [
        "foo" => [
            "a" => 1,
        ],
        "bar" => [
            "b" => 2,
            "foobar" => [
                "c" => 1,
            ]
        ],
    ];

    public function testGetAll(): void
    {
        $instance = new Configuration(
            new \Romanzaycev\Fundamenta\Components\Configuration\ArrayLoader(self::STUB),
        );
        $this->assertEquals(
            self::STUB,
            $instance->get(Configuration::ALL),
        );
    }

    public function testGetItemByPath(): void
    {
        $instance = new Configuration(
            new \Romanzaycev\Fundamenta\Components\Configuration\ArrayLoader(self::STUB),
        );
        $this->assertEquals(
            1,
            $instance->get("foo.a"),
        );
    }

    public function testGetItemByPathInSection(): void
    {
        $instance = new Configuration(
            new \Romanzaycev\Fundamenta\Components\Configuration\ArrayLoader(self::STUB),
        );
        $this->assertEquals(
            1,
            $instance->get("bar.foobar.c"),
        );
    }

    public function testGetItemByPathInSection2(): void
    {
        $instance = new Configuration(
            new \Romanzaycev\Fundamenta\Components\Configuration\ArrayLoader(self::STUB),
        );
        $this->assertEquals(
            [
                "c" => 1,
            ],
            $instance->get("bar.foobar"),
        );
    }

    public function testGetSection(): void
    {
        $instance = new Configuration(
            new \Romanzaycev\Fundamenta\Components\Configuration\ArrayLoader(self::STUB),
        );
        $this->assertEquals(
            [
                "a" => 1,
            ],
            $instance->get("foo"),
        );
    }

    public function testGetNotFoundSection(): void
    {
        $instance = new Configuration(
            new \Romanzaycev\Fundamenta\Components\Configuration\ArrayLoader(self::STUB),
        );
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Unknown configuration section: foobarbaz");
        $instance->get("foobarbaz");
    }

    public function testValidate(): void
    {
        $instance = new Configuration(
            new \Romanzaycev\Fundamenta\Components\Configuration\ArrayLoader([]),
        );
        $instance->setDefaults("foobar", [], ["a"]);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Invalid \"foobar\" section, property \"a\" is required");
        $instance->get("foobar.b");
    }

    public function testGetDefault(): void
    {
        $instance = new Configuration(
            new \Romanzaycev\Fundamenta\Components\Configuration\ArrayLoader(self::STUB),
        );
        $this->assertNull($instance->get("foo.b"));
    }

    public function testGetDefaultValue(): void
    {
        $instance = new Configuration(
            new \Romanzaycev\Fundamenta\Components\Configuration\ArrayLoader(self::STUB),
        );
        $this->assertEquals(1, $instance->get("foo.b", 1));
    }

    public function testWithDefaults(): void
    {
        $instance = new Configuration(
            new \Romanzaycev\Fundamenta\Components\Configuration\ArrayLoader([
                "foo" => [
                    "c" => 4,
                ],
                "bar" => [
                    "a" => 1,
                ],
            ]),
        );
        $instance->setDefaults("foo", [
            "a" => 1,
            "b" => 2,
            "c" => 3,
        ]);

        $this->assertEquals(
            [
                "foo" => [
                    "a" => 1,
                    "b" => 2,
                    "c" => 4,
                ],
                "bar" => [
                    "a" => 1,
                ],
            ],
            $instance->get(Configuration::ALL),
        );
        $this->assertEquals(1, $instance->get("foo.a"));
        $this->assertEquals(2, $instance->get("foo.b"));
        $this->assertEquals(4, $instance->get("foo.c"));
        $this->assertEquals(
            [
                "a" => 1,
                "b" => 2,
                "c" => 4,
            ],
            $instance->get("foo"),
        );
        $this->assertNull($instance->get("foo.d"));
        $this->assertEquals(1, $instance->get("foo.d", 1));
    }

    public function testWithDefaultsEmpty(): void
    {
        $instance = new Configuration(new \Romanzaycev\Fundamenta\Components\Configuration\ArrayLoader([]));
        $instance->setDefaults("foo", [
            "a" => 1,
        ]);
        $this->assertEquals([
            "foo" => [
                "a" => 1,
            ],
        ], $instance->get(Configuration::ALL));
    }

    public function testWithDefaultsComplex(): void
    {
        $instance = new Configuration(new \Romanzaycev\Fundamenta\Components\Configuration\ArrayLoader([
            "foo" => [
                "a" => 1,
                "b" => [
                    "c" => 2,
                ],
            ]
        ]));
        $instance->setDefaults("foo", [
            "a" => 1,
            "b" => [
                "c" => 1,
                "d" => 2,
            ],
        ]);
        $this->assertEquals([
            "foo" => [
                "a" => 1,
                "b" => [
                    "c" => 2,
                    "d" => 2,
                ],
            ],
        ], $instance->get(Configuration::ALL));
        $this->assertEquals(
            [
                "a" => 1,
                "b" => [
                    "c" => 2,
                    "d" => 2,
                ],
            ],
            $instance->get("foo"),
        );
        $this->assertEquals(2, $instance->get("foo.b.d"));
        $this->assertEquals(2, $instance->get("foo.b.c"));
    }
}
