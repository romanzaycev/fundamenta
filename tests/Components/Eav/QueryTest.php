<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Tests\Components\Eav;

use PHPUnit\Framework\TestCase;
use Romanzaycev\Fundamenta\Components\Eav\Logic;
use Romanzaycev\Fundamenta\Components\Eav\Operator;
use Romanzaycev\Fundamenta\Components\Eav\Query;

class QueryTest extends TestCase
{
    public function testWhere(): void
    {
        $instance = (new Query("foo"))
            ->where([
                // Simple
                "attr_a" => 1,

                // With operator
                "attr_b" => [2, Operator::GTE],

                "attr_c" => Logic::or([
                    // V0
                    1,

                    // V1
                    2,

                    // V2 with operator
                    [3, Operator::GTE],

                    // V3 with inner Logic
                    Logic::and([
                        "attr_c1" => "a",
                        "attr_c2" => "a",
                    ]),
                ]), // Compiled as `((attr_c = 1) OR (attr_c = 2) OR (attr_c >= 3) OR (attr_c1 = 'a' AND attr_c2 = 'a'))`

                "attr_d" => [
                    ["1", "2", "3"],
                    Operator::IN,
                ],

                Logic::or([
                    "attr_e1" => "a",
                    "attr_e2" => "a",
                ]),
            ]);

        $this
            ->assertEquals(
                "(attr_a = 1) AND (attr_b >= 2) AND ((attr_c = 1) OR (attr_c = 2) OR (attr_c >= 3) OR ((attr_c1 = 'a') AND (attr_c2 = 'a'))) AND (attr_d IN (1, 2, 3)) AND ((attr_e1 = 'a') OR (attr_e2 = 'a'))",
                $instance
                    ->getWhere()
                    ->debug()
                ,
            );
    }
}
