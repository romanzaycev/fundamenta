<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Server\OpenSwoole;

use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;

interface FilterInterface
{
    public function handle(Request $request, Response $response, RequestHandlerInterface $handler): void;

    public function getSorting(): int;
}
