<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Views\Cache;

class DataIterator extends \RecursiveIteratorIterator {
    public function __construct(array $data, int $maxDepth)
    {
        parent::__construct(
            new class ($data) extends \RecursiveArrayIterator {
                public function hasChildren(): bool
                {
                    return is_array($this->current());
                }
            },
            self::SELF_FIRST,
        );
        self::setMaxDepth($maxDepth);
    }
}
