<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Http;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;

final class HttpHelper
{
    private const PHRASES = [
        100 => 'Continue', 101 => 'Switching Protocols', 102 => 'Processing',
        200 => 'OK', 201 => 'Created', 202 => 'Accepted', 203 => 'Non-Authoritative Information', 204 => 'No Content', 205 => 'Reset Content', 206 => 'Partial Content', 207 => 'Multi-status', 208 => 'Already Reported',
        300 => 'Multiple Choices', 301 => 'Moved Permanently', 302 => 'Found', 303 => 'See Other', 304 => 'Not Modified', 305 => 'Use Proxy', 306 => 'Switch Proxy', 307 => 'Temporary Redirect',
        400 => 'Bad Request', 401 => 'Unauthorized', 402 => 'Payment Required', 403 => 'Forbidden', 404 => 'Not Found', 405 => 'Method Not Allowed', 406 => 'Not Acceptable', 407 => 'Proxy Authentication Required', 408 => 'Request Time-out', 409 => 'Conflict', 410 => 'Gone', 411 => 'Length Required', 412 => 'Precondition Failed', 413 => 'Request Entity Too Large', 414 => 'Request-URI Too Large', 415 => 'Unsupported Media Type', 416 => 'Requested range not satisfiable', 417 => 'Expectation Failed', 418 => 'I\'m a teapot', 422 => 'Unprocessable Entity', 423 => 'Locked', 424 => 'Failed Dependency', 425 => 'Unordered Collection', 426 => 'Upgrade Required', 428 => 'Precondition Required', 429 => 'Too Many Requests', 431 => 'Request Header Fields Too Large', 451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error', 501 => 'Not Implemented', 502 => 'Bad Gateway', 503 => 'Service Unavailable', 504 => 'Gateway Time-out', 505 => 'HTTP Version not supported', 506 => 'Variant Also Negotiates', 507 => 'Insufficient Storage', 508 => 'Loop Detected', 511 => 'Network Authentication Required',
    ];

    /**
     * @throws \JsonException
     */
    public static function json(array | \JsonSerializable $body, int $status = 200, ?ResponseInterface $response = null): ResponseInterface
    {
        if (!$response) {
            $response = new Response($status);
        }

        return $response
            ->withHeader("Content-Type", "application/json")
            ->withBody(Stream::create(json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)))
        ;
    }

    public static function redirect(
        string $location,
        int $status = 302,
        ?ResponseInterface $response = null,
    ): ResponseInterface
    {
        if (!$response) {
            $response = new Response($status);
        }

        return $response
            ->withAddedHeader("Location", $location)
            ->withStatus($status)
        ;
    }

    public static function getReasonPhrase(int $statusCode): string
    {
        if (isset(self::PHRASES[$statusCode])) {
            return self::PHRASES[$statusCode];
        }

        return '';
    }

    /**
     * @throws \JsonException
     */
    public static function respond(ApiAnswer $answer, ?ResponseInterface $response = null): ResponseInterface
    {
        return self::json(
            $answer,
            $response
                ? $response->getStatusCode()
                : $answer->getStatusCode(),
            $response,
        );
    }
}
