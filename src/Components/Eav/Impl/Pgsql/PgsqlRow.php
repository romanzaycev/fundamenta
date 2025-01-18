<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Impl\Pgsql;

use Psr\Log\LoggerInterface;
use Romanzaycev\Fundamenta\Components\Eav\AttributeType;
use Romanzaycev\Fundamenta\Components\Eav\Impl\Pgsql\Repositories\Helpers\PgsqlDateHelper;
use Romanzaycev\Fundamenta\Components\Eav\Row;

readonly class PgsqlRow implements Row
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        private int $id,
        private int $typeId,
        private \DateTimeInterface $createdAt,
        private \DateTimeInterface $updatedAt,
        private array $attributes,
    ) {}

    public static function create(array $item, string $entityType, Materializer $materializer, LoggerInterface $logger): self
    {
        $id = (int)($item["id"] ?? null);
        $typeId = (int)($item["type_id"] ?? null);
        $createdAt = PgsqlDateHelper::toNative($item["created_at"] ?? '') ?? new \DateTimeImmutable();
        $updatedAt = PgsqlDateHelper::toNative($item["updated_at"] ?? '') ?? new \DateTimeImmutable();
        $attributes = [];

        if (isset($item["values"])) {
            try {
                $tmp = json_decode($item["values"], true, flags: JSON_THROW_ON_ERROR);

                if (is_array($tmp)) {
                    foreach ($tmp as $attributeCode => $rawValue) {
                        if ($rawValue === null) {
                            continue;
                        }

                        if ($attribute = $materializer->getAttribute($entityType, $attributeCode)) {
                            try {
                                $attributes[$attributeCode] = match ($attribute->type) {
                                    AttributeType::VARCHAR,AttributeType::TEXT => (string)$rawValue,
                                    AttributeType::INTEGER => (int)$rawValue,
                                    AttributeType::NUMERIC => (float)$rawValue,
                                    AttributeType::BOOL => $rawValue === true || $rawValue === "true" || $rawValue === "1" || $rawValue === 1,
                                    AttributeType::DATE_TIME => PgsqlDateHelper::toNativeAttribute($rawValue),
                                    // @phpstan-ignore-next-line
                                    default => throw new \RuntimeException("Unsupported attribute type: " . $attribute->type->name),
                                };
                            } catch (\Throwable $e) {
                                $logger
                                    ->warning(
                                        "[PgsqlRow] Attribute deserialization error: " . $e->getMessage(),
                                        [
                                            "exception" => $e,
                                            "entityType" => $entityType,
                                            "attribute" => $attributeCode,
                                            "entityId" => $id,
                                        ]
                                    );
                                continue;
                            }
                        }
                    }
                }
            } catch (\JsonException $e) {
                $logger->warning("[PgsqlRow] Values deserialization error: " . $e->getMessage(), [
                    "exception" => $e,
                    "entityType" => $entityType,
                ]);
            }
        }

        return new self(
            $id,
            $typeId,
            $createdAt,
            $updatedAt,
            $attributes,
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTypeId(): int
    {
        return $this->typeId;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
