<?php declare(strict_types=1);

namespace Romanzaycev\Devsite;

use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Romanzaycev\Fundamenta\Bootstrappers\Slim;

class Site extends \Romanzaycev\Fundamenta\ModuleBootstrapper
{
    public static function router(\Slim\App $slim): void
    {
        $slim->get("/test", function (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
            return $response->withBody(Stream::create("YOLO"));
        });
    }

    public static function requires(): array
    {
        return [
            Slim::class,
        ];
    }
}
