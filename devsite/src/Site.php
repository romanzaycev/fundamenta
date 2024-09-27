<?php declare(strict_types=1);

namespace Romanzaycev\Devsite;

use DI\ContainerBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PSR7Sessions\Storageless\Http\SessionMiddleware;
use PSR7Sessions\Storageless\Session\SessionInterface;
use Romanzaycev\Fundamenta\Bootstrappers\Auth;
use Romanzaycev\Fundamenta\Bootstrappers\Slim;
use Romanzaycev\Fundamenta\Components\Auth\Context;
use Romanzaycev\Fundamenta\Components\Auth\Middlewares\AuthMiddleware;
use Romanzaycev\Fundamenta\Components\Auth\Session\SessionTokenStorageProvider;
use Romanzaycev\Fundamenta\Components\Auth\Transport\CookieTransport;
use Romanzaycev\Fundamenta\Components\Auth\Transport\UniversalTransportProvider;
use Romanzaycev\Fundamenta\Components\Http\HttpHelper;
use Romanzaycev\Fundamenta\Components\Security\CsrfHelper;
use Romanzaycev\Fundamenta\Components\Session\SessionHelper;
use Romanzaycev\Fundamenta\Components\Views\View;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\Extensions\Tooolooop;
use Slim\Exception\HttpBadRequestException;
use Slim\Routing\RouteCollectorProxy;
use function DI\autowire;

class Site extends \Romanzaycev\Fundamenta\ModuleBootstrapper
{
    public static function boot(ContainerBuilder $builder, Configuration $configuration): void
    {
        $builder->addDefinitions([
            SessionTokenStorageProvider::class => autowire(SessionTokenStorageProvider::class),
            UniversalTransportProvider::class => autowire(UniversalTransportProvider::class),
        ]);
    }

    public static function router(\Slim\App $app, View $view): void
    {
        $app->get(
            "/test",
            function (ServerRequestInterface $request, ResponseInterface $response) use ($view): ResponseInterface {
                /** @var SessionInterface $session */
                $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
                $session->set('counter', $session->get('counter', 0) + 1);

                return $response->withBody($view->renderStream("main.t.php", [
                    "text" => "YOLO!",
                    "title" => "Dynamic page",
                    "session_counter" => $session->get("counter"),
                ]));
            },
        );

        $app->group("/protected", function (RouteCollectorProxy $group) use ($view) {
            $group->get(
                "",
                function (ServerRequestInterface $request, ResponseInterface $response) use ($view): ResponseInterface {
                    /** @var Context $authContext */
                    $authContext = $request->getAttribute(AuthMiddleware::AUTH_CONTEXT_ATTRIBUTE);

                    if (!$authContext || !$authContext->getToken()) {
                        return $response->withBody($view->renderStream("protected/main.t.php", [
                            "is_authorized" => false,
                        ]))->withStatus(401);
                    }

                    return $response->withBody($view->renderStream("protected/main.t.php", [
                        "is_authorized" => true,
                        "login" => $authContext->getToken()->getPayload()["login"],
                        "csrf_token" => CsrfHelper::ensureToken(SessionHelper::getSession($request)),
                    ]));
                }
            );
            $group->post(
                "",
                function (ServerRequestInterface $request, ResponseInterface $response) use ($view): ResponseInterface {
                    /** @var Context $authContext */
                    $authContext = $request->getAttribute(AuthMiddleware::AUTH_CONTEXT_ATTRIBUTE);

                    if ($authContext && $authContext->getToken()) {
                        return HttpHelper::redirect("/protected");
                    }

                    $params = $request->getParsedBody();
                    $login = $params["login"] ?? "";
                    $password = $params["password"] ?? "";

                    if ($login !== "user" || $password !== "password") {
                        return $response->withBody($view->renderStream("protected/main.t.php", [
                            "is_authorized" => false,
                            "error" => "Invalid login or password",
                        ]))->withStatus(401);
                    }

                    $authContext->start($authContext->getStorage()->create([
                        "login" => $login,
                    ], (new \DateTimeImmutable())->add(new \DateInterval("PT24H"))), CookieTransport::class);

                    return HttpHelper::redirect("/protected", response: $response);
                }
            );
            $group->post(
                "/logout",
                function (ServerRequestInterface $request, ResponseInterface $response) use ($view): ResponseInterface {
                    /** @var Context $authContext */
                    $authContext = $request->getAttribute(AuthMiddleware::AUTH_CONTEXT_ATTRIBUTE);

                    if (!$authContext || !$authContext->getToken()) {
                        return $response->withStatus(401);
                    }

                    $session = SessionHelper::getSession($request);

                    if (!CsrfHelper::validate($request, $session)) {
                        throw new HttpBadRequestException($request);
                    }

                    $authContext->close();
                    CsrfHelper::remove($session);

                    return HttpHelper::redirect("/protected", response: $response);
                }
            );
        });
    }

    public static function requires(): array
    {
        return [
            Auth::class,
            Slim::class,
            Tooolooop\Bootstrapper::class,
        ];
    }
}
