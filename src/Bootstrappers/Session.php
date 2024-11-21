<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Bootstrappers;

use Dflydev\FigCookies\Modifier\SameSite;
use Dflydev\FigCookies\SetCookie;
use DI\Container;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use PSR7Sessions\Storageless\Http\ClientFingerprint\RemoteAddr;
use PSR7Sessions\Storageless\Http\ClientFingerprint\UserAgent;
use PSR7Sessions\Storageless\Http\Configuration as StoragelessConfig;
use PSR7Sessions\Storageless\Http\ClientFingerprint\Configuration as FingerprintConfig;
use Romanzaycev\Fundamenta\Components\Session\FndaSessionMiddleware;
use Romanzaycev\Fundamenta\Components\Startup\HookManager;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\Exceptions\Domain\InvalidParamsException;
use Romanzaycev\Fundamenta\ModuleBootstrapper;
use Slim\App;

class Session extends ModuleBootstrapper
{
    public static function preconfigure(Configuration $configuration): void
    {
        $configuration->setDefaults(
            "session",
            [
                "ttl" => 30 * 24 * 60,
                "fingerprint" => [
                    "enabled" => false,
                    "mode" => "ua", // or "ip", "ip_ua", "custom",
                    "custom_sources_class" => null,
                ],
                "cookie" => [
                    "secure" => true,
                    "name" => "session_dat",
                    "path" => "/",
                    "http_only" => true,
                    "same_site" => "none", // none|lax|strict,
                ],
            ],
            [
                "ttl",
                "secret_key",
                "cookie",
                "cookie.name",
            ]
        );
    }

    /**
     * @throws \Throwable
     */
    public static function middlewares(App $app, Configuration $configuration, Container $container): array
    {
        $cookieParams = $configuration->get("session.cookie");
        $config = (new StoragelessConfig(
            \Lcobucci\JWT\Configuration::forSymmetricSigner(
                new Sha256(),
                InMemory::base64Encoded($configuration->get("session.secret_key")),
            )
        ))
            ->withCookie(
                SetCookie::create($cookieParams["name"])
                    ->withSecure($cookieParams["secure"])
                    ->withHttpOnly($cookieParams["http_only"])
                    ->withPath($cookieParams["path"])
                    ->withSameSite(SameSite::fromString($cookieParams["same_site"])),
            )
            ->withIdleTimeout($configuration->get("session.ttl"))
        ;

        if ($configuration->get("session.fingerprint.enabled", false)) {
            $mode = $configuration->get("session.fingerprint.mode", "ua");

            switch ($mode) {
                case "ua":
                    $config->withClientFingerprintConfiguration(FingerprintConfig::forSources(new UserAgent()));
                    break;

                case "ip":
                    $config->withClientFingerprintConfiguration(FingerprintConfig::forSources(new RemoteAddr()));
                    break;

                case "ip_ua":
                    $config->withClientFingerprintConfiguration(FingerprintConfig::forIpAndUserAgent());
                    break;

                case "custom":
                    $customSourcesClass = $configuration->get("session.fingerprint.custom_sources_class");

                    if (!$customSourcesClass) {
                        throw new InvalidParamsException();
                    }

                    $config->withClientFingerprintConfiguration(FingerprintConfig::forSources(
                        $container->get($customSourcesClass),
                    ));
                    break;

                default:
                    throw new InvalidParamsException("Unknown sessions fingerprinting mode: " . $mode);
            }
        }

        return [new FndaSessionMiddleware(
            $config,
            $container->get(HookManager::class),
            $container,
        )];
    }

    public static function requires(): array
    {
        return [
            Dotenv::class,
            Monolog::class,
            Slim::class,
        ];
    }
}
