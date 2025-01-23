<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Security;

use Psr\Http\Message\ServerRequestInterface;
use PSR7Sessions\Storageless\Session\SessionInterface;

final class CsrfHelper
{
    /**
     * @throws \Throwable
     */
    public static function ensureToken(SessionInterface $session): string
    {
        if ($session->has("csrf-token")) {
            return $session->get("csrf-token");
        }

        $token = bin2hex(random_bytes(16));
        $session->set("csrf-token", $token);

        return $token;
    }

    public static function removeFrom(SessionInterface $session): void
    {
        $session->remove("csrf-token");
    }

    public static function validate(ServerRequestInterface $request, SessionInterface $session): bool
    {
        $params = $request->getParsedBody();
        $csrfToken = $params["csrf-token"] ?? null;

        if (!$csrfToken) {
            return false;
        }

        if ($session->get("csrf-token") !== $csrfToken) {
            return false;
        }

        return true;
    }
}
