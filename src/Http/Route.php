<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Http;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
readonly class Route
{
    /**
     * @param string[] $method
     */
    public function __construct(
        public string $pattern,
        public array $method = ["GET"],
        public ?string $group = null,
    ) {}
}
