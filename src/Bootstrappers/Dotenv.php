<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Bootstrappers;

use Dotenv\Dotenv as D;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\ModuleBootstrapper;

class Dotenv extends ModuleBootstrapper
{
    public static function preconfigure(Configuration $configuration): void
    {
        D::createImmutable($configuration->get("dotenv.path"))->load();
    }
}
