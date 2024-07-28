<?php declare(strict_types=1);

namespace Romanzaycev\Devsite;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Romanzaycev\Fundamenta\Bootstrappers\Slim;
use Romanzaycev\Fundamenta\Components\Views\View;
use Romanzaycev\Fundamenta\Extensions\Tooolooop;

class Site extends \Romanzaycev\Fundamenta\ModuleBootstrapper
{
    public static function afterContainerBuilt(\Slim\App $slim, View $view): void
    {
        $slim->get(
            "/test",
            function (ServerRequestInterface $request, ResponseInterface $response, array $args) use ($view): ResponseInterface {
                return $response->withBody($view->renderStream("main.t.php", [
                    "text" => "YOLO!",
                    "title" => "Dynamic page",
                ]));
            },
        );
    }

    public static function requires(): array
    {
        return [
            Slim::class,
            Tooolooop\Bootstrapper::class,
        ];
    }
}
