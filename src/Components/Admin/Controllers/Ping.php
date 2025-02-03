<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Admin\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Romanzaycev\Fundamenta\Components\Http\GenericApiAnswer;
use Romanzaycev\Fundamenta\Components\Http\HttpHelper;

class Ping
{
    /**
     * @throws \Throwable
     */
    public function isAlive(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return HttpHelper::respond(GenericApiAnswer::success(["is_alive" => true]), $response);
    }
}
