<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Romanzaycev\Fundamenta\Configuration;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class ArrayAdapterFactory implements AdapterFactory
{
    public function get(Configuration $configuration): CacheItemPoolInterface
    {
        $options = $configuration->get("cache.options.array");

        return new ArrayAdapter(
            $options["default_lifetime"],
            $options["store_serialized"],
            $options["max_lifetime"],
            $options["max_items"],
        );
    }
}
