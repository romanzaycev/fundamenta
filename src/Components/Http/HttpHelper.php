<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Http;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

final class HttpHelper
{
    /**
     * @throws \JsonException
     */
    public static function json(array | \JsonSerializable $body, int $status = 200): Response
    {
        return new Response(
            $status,
            [
                "Content-Type" => "application/json",
            ],
            json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        );
    }

    public static function redirect(string $location, int $status = 302, ?ResponseInterface $response = null): Response
    {
        if (!$response) {
            $response = new Response($status);
        }

        return $response
            ->withAddedHeader("Location", $location)
            ->withStatus($status)
        ;
    }
}
