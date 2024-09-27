<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Auth\Session;

use Psr\Http\Message\ServerRequestInterface;
use PSR7Sessions\Storageless\Http\SessionMiddleware;
use PSR7Sessions\Storageless\Session\SessionInterface;
use Romanzaycev\Fundamenta\Components\Auth\Exceptions\TokenStorageException;
use Romanzaycev\Fundamenta\Components\Auth\Token;
use Romanzaycev\Fundamenta\Components\Auth\TokenStorage;

class SessionTokenStorage implements TokenStorage
{
    private const TOKEN_KEY = "tk";

    public function __construct(
        private readonly ServerRequestInterface $request,
    ) {}

    public function get(string $id): ?Token
    {
        try {
            /** @var SessionInterface $session */
            $session = $this->request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
            $tokenData = $session->get(self::TOKEN_KEY);

            if ($tokenData === null) {
                return null;
            }

            $token = SessionToken::unpack($tokenData);
        } catch (\Throwable $e) {
            throw new TokenStorageException("Unable to load session token", (int)$e->getCode(), $e);
        }

        if (!\hash_equals($token->getID(), $id)) {
            return null;
        }

        $expiresAt = $token->expiresAt();

        if ($expiresAt !== null && $expiresAt < new \DateTimeImmutable()) {
            $this->delete($token);

            return null;
        }

        return $token;
    }

    public function create(array $payload, \DateTimeInterface $expiresAt): Token
    {
        try {
            $token = new SessionToken(
                \substr(\bin2hex(\random_bytes(64)), 0, 64),
                $payload,
                $expiresAt,
            );
            /** @var SessionInterface $session */
            $session = $this->request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
            $session->set(self::TOKEN_KEY, $token->pack());

            return $token;
        } catch (\Throwable $e) {
            throw new TokenStorageException("Unable to create session token", (int)$e->getCode(), $e);
        }
    }

    public function delete(Token $token): void
    {
        /** @var SessionInterface $session */
        $session = $this->request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $session->remove(self::TOKEN_KEY);
    }
}
