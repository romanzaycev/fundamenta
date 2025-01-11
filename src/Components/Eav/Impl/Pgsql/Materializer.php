<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Impl\Pgsql;

use Romanzaycev\Fundamenta\Components\Eav\Attribute;
use Romanzaycev\Fundamenta\Components\Eav\AttributeHelper;
use Romanzaycev\Fundamenta\Components\Eav\Events\AttributeCreated;
use Romanzaycev\Fundamenta\Components\Eav\Events\AttributeDeleted;
use Romanzaycev\Fundamenta\Components\Eav\Repositories\AttributeRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Materializer
{
    /** @var array<string, EntityMetadata> */
    private array $metadata = [];

    public function __construct(
        private readonly EventDispatcher $ed,
        private readonly AttributeRepositoryInterface $attributeRepository,
    )
    {
        $this
            ->ed
            ->addListener(
                AttributeCreated::EVENT,
                $this->invalidate(...),
            );
        $this
            ->ed
            ->addListener(
                AttributeDeleted::EVENT,
                $this->invalidate(...),
            );
    }

    public function isKnownAttribute(string $entityType, string $code): bool
    {
        $code = AttributeHelper::normalizeAttributeCode($code);
        $this->loadMetadata($entityType);

        return isset($this->metadata[$entityType], $this->metadata[$entityType]->attributes[$code]);
    }

    public function getAttribute(string $entityType, string $code): ?Attribute
    {
        $code = AttributeHelper::normalizeAttributeCode($code);
        $this->loadMetadata($entityType);

        if (isset($this->metadata[$entityType], $this->metadata[$entityType]->attributes[$code])) {
            return $this->metadata[$entityType]->attributes[$code];
        }

        return null;
    }

    private function loadMetadata(string $entityType): void
    {
        if (isset($this->metadata[$entityType])) {
            return;
        }

        $attributeMap = [];

        foreach ($this->attributeRepository->getListByEntityType($entityType) as $attribute) {
            $attributeMap[$attribute->code] = $attribute;
        }

        $this->metadata[$entityType] = new EntityMetadata(
            $attributeMap,
        );
    }

    private function invalidate(): void
    {
        $this->metadata = [];
    }

    public function __destruct()
    {
        $this->ed->removeListener(AttributeCreated::EVENT, $this->invalidate(...));
        $this->ed->removeListener(AttributeDeleted::EVENT, $this->invalidate(...));
    }
}
