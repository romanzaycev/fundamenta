<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Startup;

use DI\Container;

interface HookManager
{
    public const ON_REQUEST = "on_request";
    public const ON_SESSION_STARTED = "on_session_started";
    public const ON_REQUEST_TERMINATED = "on_request_terminated";

    public function add(string $hook, callable $handler): void;
    public function remove(string $hook, callable $handler): void;
    public function call(Container $container, string $hook, mixed $data = null): void;
}
