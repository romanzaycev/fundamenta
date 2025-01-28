<?php declare(strict_types=1);

namespace Romanzaycev\Devsite\UseCases\Protected;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Romanzaycev\Fundamenta\Components\Auth\AuthHelper;
use Romanzaycev\Fundamenta\Components\Auth\Transport\CookieTransport;
use Romanzaycev\Fundamenta\Components\Http\HttpHelper;
use Romanzaycev\Fundamenta\Components\Security\CsrfHelper;
use Romanzaycev\Fundamenta\Components\Session\SessionHelper;
use Romanzaycev\Fundamenta\Components\Views\View;
use Slim\Exception\HttpBadRequestException;

readonly class Controller
{
    public function __construct(
        private View $view,
    ) {}

    /**
     * @throws \Throwable
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $isAuthorized = AuthHelper::isAuthorized($request);

        return $response->withBody($this->view->renderStream("protected/main.t.php", [
            "is_authorized" => $isAuthorized,
            "login" => AuthHelper::getContext($request)?->getToken()?->getPayload()["login"],
            "csrf_token" => CsrfHelper::ensureToken(SessionHelper::getSession($request)),
        ]))->withStatus($isAuthorized ? 200 : 401);
    }

    /**
     * @throws \Throwable
     */
    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (AuthHelper::isAuthorized($request)) {
            return HttpHelper::redirect("/protected");
        }

        $params = $request->getParsedBody();
        $login = $params["login"] ?? "";
        $password = $params["password"] ?? "";

        if ($login !== "user" || $password !== "password") {
            return $response->withBody($this->view->renderStream("protected/main.t.php", [
                "is_authorized" => false,
                "error" => "Invalid login or password",
            ]))->withStatus(401);
        }

        $authContext = AuthHelper::getContext($request);
        $authContext->start($authContext->getStorage()->create([
            "login" => $login,
        ], (new \DateTimeImmutable())->add(new \DateInterval("PT24H"))), CookieTransport::class);

        return HttpHelper::redirect("/protected", response: $response);
    }

    /**
     * @throws \Throwable
     */
    public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $authContext = AuthHelper::getContext($request);
        $session = SessionHelper::getSession($request);

        if (!CsrfHelper::validate($request, $session)) {
            throw new HttpBadRequestException($request);
        }

        $authContext->close();
        CsrfHelper::removeFrom($session);

        return HttpHelper::redirect("/protected", response: $response);
    }
}
