<?php declare(strict_types=1);

namespace Romanzaycev\Devsite\UseCases\Test;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PSR7Sessions\Storageless\Http\SessionMiddleware;
use PSR7Sessions\Storageless\Session\SessionInterface;
use Romanzaycev\Fundamenta\Components\Views\View;

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
        /** @var SessionInterface $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $session->set('counter', $session->get('counter', 0) + 1);

        return $response->withBody($this->view->renderStream("main.t.php", [
            "text" => "YOLO!",
            "title" => "Dynamic page",
            "session_counter" => $session->get("counter"),
        ]));
    }
}
