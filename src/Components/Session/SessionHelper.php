<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Session;

use Psr\Http\Message\ServerRequestInterface;
use PSR7Sessions\Storageless\Session\SessionInterface;

final class SessionHelper
{
    public static function getSession(ServerRequestInterface $request): SessionInterface
    {
        return $request->getAttribute(FundamentaSessionMiddleware::SESSION_ATTRIBUTE);
    }
}
