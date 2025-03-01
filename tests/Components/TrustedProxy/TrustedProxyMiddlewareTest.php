<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Tests\Components\TrustedProxy;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Romanzaycev\Fundamenta\Components\TrustedProxy\TrustedProxyMiddleware;

class TrustedProxyMiddlewareTest extends TestCase
{
    public function testWrongProxy(): void
    {
        $request = new ServerRequest(
            "GET",
            "https://example.com/",
            [
                "X-Forwarded-For" => "200.100.10.20",
            ],
            serverParams: [
                "REMOTE_ADDR" => "192.168.0.1",
            ],
        );

        $instance = new TrustedProxyMiddleware(
            ["127.0.0.1"],
        );
        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return (new Response())
                    ->withHeader(
                        "Rq-IP",
                        (string)$request->getAttribute(TrustedProxyMiddleware::CLIENT_IP_ATTRIBUTE),
                    );
            }
        };
        $response = $instance->process($request, $handler);

        $this->assertEquals("", $response->getHeaderLine("Rq-IP"));
    }

    public function testValidProxy(): void
    {
        $request = new ServerRequest(
            "GET",
            "https://example.com/",
            [
                "X-Forwarded-For" => "200.100.10.20",
            ],
            serverParams: [
                "REMOTE_ADDR" => "127.0.0.2",
            ],
        );

        $instance = new TrustedProxyMiddleware(
            ["127.0.0.0/24"],
        );
        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return (new Response())
                    ->withHeader(
                        "Rq-IP",
                        (string)$request->getAttribute(TrustedProxyMiddleware::CLIENT_IP_ATTRIBUTE),
                    );
            }
        };
        $response = $instance->process($request, $handler);

        $this->assertEquals("200.100.10.20", $response->getHeaderLine("Rq-IP"));
    }
}
