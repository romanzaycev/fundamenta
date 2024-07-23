<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Romanzaycev\Fundamenta\Configuration;

interface AdapterFactory
{
    public function get(Configuration $configuration): CacheItemPoolInterface;
}
