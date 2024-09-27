<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Auth\Transport;

use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\Modifier\SameSite;
use Dflydev\FigCookies\SetCookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class CookieTransport implements HttpTransport
{
    public function __construct(
        private string $cookie,
        private string $basePath = '/',
        private ?string $domain = null,
        private bool $secure = false,
        private bool $httpOnly = true,
        private ?string $sameSite = null,
    ) {}

    public function get(ServerRequestInterface $request): ?string
    {
        return $request->getCookieParams()[$this->cookie] ?? null;
    }

    public function commit(
        ServerRequestInterface $request,
        ResponseInterface $response,
        ?string $id,
        \DateTimeInterface $expiresAt = null,
    ): ResponseInterface
    {
        $setCookie = SetCookie::create($this->cookie)
            ->withValue($id)
            ->withExpires($expiresAt)
            ->withPath($this->basePath)
            ->withDomain($this->domain)
            ->withSecure($this->secure)
            ->withHttpOnly($this->httpOnly)
        ;

        if ($this->sameSite !== null) {
            $setCookie = $setCookie->withSameSite(SameSite::fromString($this->sameSite));
        }

        return FigResponseCookies::set($response, $setCookie);
    }

    public function remove(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $id,
    ): ResponseInterface
    {
        return FigResponseCookies::set(
            $response,
            SetCookie::create($this->cookie)
                ->withExpires((new \DateTimeImmutable())->sub(new \DateInterval("P5Y")))
                ->withPath($this->basePath)
                ->withDomain($this->domain)
                ->withSecure($this->secure)
                ->withHttpOnly($this->httpOnly)
        );
    }
}
