<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Auth;

use Romanzaycev\Fundamenta\Components\Auth\Exceptions\TokenStorageException;

interface TokenStorage
{
    /**
     * @throws TokenStorageException
     */
    public function get(string $id): ?Token;

    /**
     * @throws TokenStorageException
     */
    public function create(
        array $payload,
        \DateTimeInterface $expiresAt,
    ): Token;

    public function delete(Token $token): void;
}
