<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Admin\Internals\Auth;

use DI\Container;
use Psr\Http\Message\ServerRequestInterface;
use Romanzaycev\Fundamenta\Components\Auth\TokenStorageLifecycle;
use Romanzaycev\Fundamenta\Components\Auth\Token;
use Romanzaycev\Fundamenta\Components\Auth\TokenStorage;
use Romanzaycev\Fundamenta\Components\Auth\TokenStorageProvider;
use Romanzaycev\Fundamenta\Components\Auth\TokenStorageSelector;
use Romanzaycev\Fundamenta\Configuration;

class AdminTokenStorage implements TokenStorageSelector, TokenStorageProvider, TokenStorage
{
    private string $apiBasePath;

    /** @var array<string, Token> */
    private array $data = [];

    public function __construct(
        private readonly Configuration $configuration,
    )
    {
        $this->apiBasePath = $this->configuration->get("admin.paths.ui_api_base_path");
    }

    public function get(string $id): ?Token
    {
        return $this->data[$id] ?? null;
    }

    public function create(array $payload, \DateTimeInterface $expiresAt): Token
    {
        $instance = new AdminToken($expiresAt, $payload);
        $this->data[$instance->getId()] = $instance;

        return $instance;
    }

    public function delete(Token $token): void
    {
        unset($this->data[$token->getId()]);
    }

    /**
     * @throws \Throwable
     */
    public function createPersistent(Container $container): TokenStorage
    {
        return $this;
    }

    public function createForRequest(ServerRequestInterface $request, Container $container): TokenStorage
    {
        throw new \RuntimeException("Not applicable");
    }

    public function getLifecycle(): TokenStorageLifecycle
    {
        return TokenStorageLifecycle::PERSISTENT;
    }

    public function select(ServerRequestInterface $request, array $storages): ?string
    {
        if (\str_starts_with($request->getRequestTarget(), $this->apiBasePath)) {
            return self::class;
        }

        return null;
    }
}
