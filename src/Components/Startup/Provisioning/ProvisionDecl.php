<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Startup\Provisioning;

readonly class ProvisionDecl
{
    /**
     * @template T
     * @param class-string<T> $providerInterfaceClass
     * @param \Closure(T[]): void $acceptor
     */
    public function __construct(
        public string $providerInterfaceClass,
        public \Closure $acceptor,
    ) {}
}
