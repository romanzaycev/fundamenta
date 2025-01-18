<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Impl\Pgsql;

use Romanzaycev\Fundamenta\Components\Eav\Attribute;
use Romanzaycev\Fundamenta\Components\Eav\AttributeHelper;
use Romanzaycev\Fundamenta\Components\Eav\Events\AttributeCreated;
use Romanzaycev\Fundamenta\Components\Eav\Events\AttributeDeleted;
use Romanzaycev\Fundamenta\Components\Eav\Exceptions\QueryException;
use Romanzaycev\Fundamenta\Components\Eav\Repositories\AttributeRepositoryInterface;
use Romanzaycev\Fundamenta\Components\Eav\Repositories\TypeRepositoryInterface;
use Romanzaycev\Fundamenta\Components\Eav\Type;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Materializer
{
    /** @var array<string, EntityMetadata> */
    private array $metadata = [];

    /** @var array<int, Type> */
    private array $typeCache = [];

    public function __construct(
        private readonly EventDispatcher $ed,
        private readonly TypeRepositoryInterface $typeRepository,
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

    public function getAttribute(string $entityType, string $code): ?Attribute
    {
        $code = AttributeHelper::normalizeAttributeCode($code);
        $this->loadMetadata($entityType);

        if (isset($this->metadata[$entityType], $this->metadata[$entityType]->attributes[$code])) {
            return $this->metadata[$entityType]->attributes[$code];
        }

        return null;
    }

    public function isKnownAttribute(string $entityType, string $code): bool
    {
        $code = AttributeHelper::normalizeAttributeCode($code);
        $this->loadMetadata($entityType);

        return isset($this->metadata[$entityType], $this->metadata[$entityType]->attributes[$code]);
    }

    /**
     * @throws QueryException
     */
    public function getTypeIdByCode(string $entityTypeCode): int
    {
        if (isset($this->typeCache[$entityTypeCode])) {
            return $this->typeCache[$entityTypeCode]->id;
        }

        $type = $this->typeRepository->findByCode($entityTypeCode);

        if (!$type) {
            throw new QueryException(sprintf(
                "Type \"%s\" not found",
                $entityTypeCode,
            ));
        }

        $this->typeCache[$entityTypeCode] = $type;

        return $type->id;
    }

    private function loadMetadata(string $entityTypeCode): void
    {
        if (isset($this->metadata[$entityTypeCode])) {
            return;
        }

        $attributeMap = [];

        foreach ($this->attributeRepository->getListByEntityTypeCode($entityTypeCode) as $attribute) {
            $attributeMap[$attribute->code] = $attribute;
        }

        $this->metadata[$entityTypeCode] = new EntityMetadata(
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
