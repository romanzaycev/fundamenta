<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Server\OpenSwoole;

use Dflydev\FigCookies\SetCookies;
use OpenSwoole\Core\Psr\Response;

class SwoolePsrResponseHelper extends Response
{
    public static function emit(\OpenSwoole\HTTP\Response $response, $psrResponse)
    {
        $response->status($psrResponse->getStatusCode());
        $cookies = [];

        foreach ($psrResponse->getHeaders() as $name => $values) {
            $headerName = strtolower($name);

            foreach ($values as $value) {
                if ($headerName === "set-cookie") {
                    $cookies[] = $value;
                } else {
                    $response->header($name, $value);
                }
            }
        }

        if (!empty($cookies)) {
            foreach (SetCookies::fromSetCookieStrings($cookies)->getAll() as $cookie) {
                $response->cookie(
                    $cookie->getName(),
                    $cookie->getValue(),
                    $cookie->getExpires(),
                    $cookie->getPath() ?: '/',
                    $cookie->getDomain() ?: '',
                    $cookie->getSecure(),
                    $cookie->getHttpOnly()
                );
            }
        }

        $body = $psrResponse->getBody();
        $body->rewind();

        if ($body->getSize() > static::CHUNK_SIZE) {
            while (!$body->eof()) {
                $response->write($body->read(static::CHUNK_SIZE));
            }

            $response->end();
        } else {
            $response->end($body->getContents());
        }
    }
}
