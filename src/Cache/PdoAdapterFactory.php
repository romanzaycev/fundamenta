<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Romanzaycev\Fundamenta\Configuration;
use Symfony\Component\Cache\Adapter\PdoAdapter;

class PdoAdapterFactory implements AdapterFactory
{
    public function __construct(
        private readonly \PDO $pdo,
        private readonly LoggerInterface $logger,
    ) {}

    public function get(Configuration $configuration): CacheItemPoolInterface
    {
        $adapter = new PdoAdapter(
            $this->pdo,
            options: $configuration->get("cache.pdo_adapter", []),
        );
        $adapter->setLogger($this->logger);

        return $adapter;
    }
}
