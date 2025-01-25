<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Server\OpenSwoole;

use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;

interface RequestHandlerInterface
{
    public function handle(Request $request, Response $response): void;
}
