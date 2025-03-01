<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Bootstrappers;

use Romanzaycev\Fundamenta\Components\TrustedProxy\TrustedProxyMiddleware;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\ModuleBootstrapper;

class TrustedProxy extends ModuleBootstrapper
{
    public static function preconfigure(Configuration $configuration): void
    {
        $configuration->setDefaults(
            "trusted-proxy",
            [
                "enabled" => false,
                "proxies" => [],
                "header" => "X-Forwarded-For",
                "attribute_name" => TrustedProxyMiddleware::CLIENT_IP_ATTRIBUTE,
            ],
            [
                "enabled",
                "proxies",
                "header",
                "attribute_name",
            ]
        );
    }

    public static function middlewares(Configuration $configuration): array
    {
        $config = $configuration->get("trusted-proxy");

        if ($config["enabled"] === true) {
            return [new TrustedProxyMiddleware(
                $config["proxies"],
                $config["header"],
                $config["attribute_name"],
            )];
        }

        return [];
    }
}
