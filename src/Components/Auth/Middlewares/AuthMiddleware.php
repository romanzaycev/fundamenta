<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Auth\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Romanzaycev\Fundamenta\Components\Auth\Context;
use Romanzaycev\Fundamenta\Components\Auth\Exceptions\TokenStorageException;
use Romanzaycev\Fundamenta\Components\Auth\TokenStorage;
use Romanzaycev\Fundamenta\Components\Auth\TokenStorageSource;
use Romanzaycev\Fundamenta\Components\Auth\TransportSource;
use Romanzaycev\Fundamenta\Exceptions\Domain\EntityNotFoundException;

class AuthMiddleware implements MiddlewareInterface
{
    public const AUTH_CONTEXT_ATTRIBUTE = "auth_context";

    /**
     * @param class-string<TokenStorage> $tokenStorageClass
     */
    public function __construct(
        private readonly TokenStorageSource $tokenStorageSource,
        private readonly TransportSource $transportSource,
        private readonly string $tokenStorageClass,
        private readonly string $defaultTransport,
    ) {}

    /**
     * @throws TokenStorageException
     * @throws EntityNotFoundException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $storage = $this->tokenStorageSource->getStorage($this->tokenStorageClass);
        $ctx = new Context($storage, $this->defaultTransport);
        $request = $request->withAttribute(self::AUTH_CONTEXT_ATTRIBUTE, $ctx);

        foreach ($this->transportSource->getTransports() as $transport) {
            $id = $transport->get($request);

            if (!$id) {
                continue;
            }

            $token = $storage->get($id);

            if ($token === null) {
                continue;
            }

            $ctx->start($token, $transport::class);
            break;
        }

        $response = $handler->handle($request);

        if ($transportClass = $ctx->getTransport()) {
            $transport = $this->transportSource->getTransport($transportClass);

            if ($ctx->isClosed()) {
                $storage->delete($ctx->getToken());

                return $transport->remove(
                    $request,
                    $response,
                    $ctx->getToken()->getId(),
                );
            }

            $token = $ctx->getToken();

            if ($token) {
                return $transport->commit(
                    $request,
                    $response,
                    $token->getId(),
                    $token->expiresAt(),
                );
            }
        }

        return $response;
    }
}
