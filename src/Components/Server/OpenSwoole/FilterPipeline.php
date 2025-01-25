<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Server\OpenSwoole;

use DI\Container;
use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;

class FilterPipeline implements FilterCollection
{
    /** @var array<FilterInterface> */
    private array $filters = [];
    private ?RequestHandlerInterface $chain = null;

    public function __construct(
        private readonly FinalHandler $finalHandler,
        private readonly Container $container,
    ) {}

    /**
     * @param FilterInterface|class-string<FilterInterface> $filter
     * @throws \Throwable
     */
    public function add(FilterInterface|string $filter): void
    {
        if (is_string($filter)) {
            $filter = $this->container->get($filter);
        }

        $this->filters[] = $filter;
        usort(
            $this->filters,
            static fn (FilterInterface $a, FilterInterface $b): int => $a->getSorting() <=> $b->getSorting(),
        );
        $this->chain = null;
    }

    public function handle(Request $request, Response $response, callable $callback): void
    {
        $this->finalHandler->setCallback($callback);

        try {
            $this
                ->buildHandlerChain()
                ->handle(
                    $request,
                    $response,
                )
            ;
        } finally {
            $this->finalHandler->setCallback(null);
        }
    }

    private function buildHandlerChain(): RequestHandlerInterface
    {
        if ($this->chain) {
            return $this->chain;
        }

        $handler = new class ($this->finalHandler) implements RequestHandlerInterface {
            public function __construct(
                private readonly RequestHandlerInterface $handler,
            ) {}

            public function handle(Request $request, Response $response): void
            {
                $this->handler->handle($request, $response);
            }
        };

        foreach (array_reverse($this->filters) as $filter) {
            $handler = new class ($filter, $handler) implements RequestHandlerInterface {
                public function __construct(
                    private readonly FilterInterface $filter,
                    private readonly RequestHandlerInterface $next,
                ) {}

                public function handle(Request $request, Response $response): void
                {
                    $this
                        ->filter
                        ->handle(
                            $request,
                            $response,
                            $this->next,
                        );
                }
            };
        }

        $this->chain = $handler;

        return $handler;
    }
}
