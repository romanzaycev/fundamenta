<?php

namespace Romanzaycev\Fundamenta\Components\TrustedProxy;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Wikimedia\IPSet;

class TrustedProxyMiddleware implements MiddlewareInterface
{
    public const CLIENT_IP_ATTRIBUTE = "client_ip";

    private IPSet $ipSet;

    /**
     * @param string[] $trustedProxies
     */
    public function __construct(
        private readonly array $trustedProxies,
        private readonly string $header = "X-Forwarded-For",
        private readonly string $attributeName = self::CLIENT_IP_ATTRIBUTE,
    )
    {
        $this->ipSet = new IPSet($this->trustedProxies);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->isTrustedProxy($request->getServerParams()['REMOTE_ADDR'] ?? "")) {
            $request = $this->updateRequest($request);
        }

        return $handler->handle($request);
    }

    private function isTrustedProxy(mixed $remoteAddr): bool
    {
        return $this->ipSet->match($remoteAddr);
    }

    private function updateRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        $ip = $this->getClientIp($request);

        if ($ip) {
            $request = $request->withAttribute(
                $this->attributeName,
                $ip,
            );
        }

        return $request;
    }

    private function getClientIp(ServerRequestInterface $request): ?string
    {
        $headers = $request->getHeader($this->header);

        if (empty($headers)) {
            return null;
        }

        $ips = explode(',', $headers[0]);
        $ip = trim($ips[0]);

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : null;
    }
}
