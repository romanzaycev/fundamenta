<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Startup;

use DI\Container;
use Psr\Log\LoggerInterface;

class ApplicationHookManager implements HookManager
{
    /**
     * @var array<string, callable[]>
     */
    private array $hooks = [];

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function add(string $hook, callable $handler): void
    {
        if (!isset($this->hooks[$hook])) {
            $this->hooks[$hook] = [];
        }

        $this->hooks[$hook][] = $handler;
    }

    public function remove(string $hook, callable $handler): void
    {
        if (isset($this->hooks[$hook])) {
            foreach ($this->hooks[$hook] as $i => $callable) {
                if ($handler == $callable) {
                    unset($this->hooks[$hook][$i]);
                }
            }
        }
    }

    public function call(Container $container, string $hook, mixed $data = null): void
    {
        if (isset($this->hooks[$hook])) {
            foreach ($this->hooks[$hook] as $callable) {
                try {
                    $container->call($callable, [
                        $data,
                    ]);
                } catch (\Throwable $e) {
                    $this
                        ->logger
                        ->error(sprintf(
                            "[ApplicationHookManager] (%s) Handler error: %s",
                            $hook,
                            $e->getMessage(),
                        ), [
                            "exception" => $e,
                        ]);
                }
            }
        }
    }

    public function close(): void
    {
        $this->hooks = [];
    }
}
