<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Auth;

use Romanzaycev\Fundamenta\Exceptions\Domain\EntityNotFoundException;

interface TokenStorageSource
{
    /**
     * @return TokenStorage[]
     */
    public function getStorages(): array;

    /**
     * @param class-string<TokenStorage> $class
     * @throws EntityNotFoundException
     */
    public function getStorage(string $class): TokenStorage;

    /**
     * @return class-string<TokenStorage>[]
     */
    public function getClasses(): array;
}
