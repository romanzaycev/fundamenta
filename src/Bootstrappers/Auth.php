<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Bootstrappers;

use DI\Container;
use DI\ContainerBuilder;
use Psr\Http\Message\ServerRequestInterface;
use Romanzaycev\Fundamenta\Components\Auth\Internals\DefaultUserProviderHolder;
use Romanzaycev\Fundamenta\Components\Auth\Internals\TokenStorageHolder;
use Romanzaycev\Fundamenta\Components\Auth\Internals\TokenTransportHolder;
use Romanzaycev\Fundamenta\Components\Auth\Middlewares\AuthedContextMiddleware;
use Romanzaycev\Fundamenta\Components\Auth\TokenStorageLifecycle;
use Romanzaycev\Fundamenta\Components\Auth\TokenStorageProvider;
use Romanzaycev\Fundamenta\Components\Auth\TokenStorageSelector;
use Romanzaycev\Fundamenta\Components\Auth\TokenTransportProvider;
use Romanzaycev\Fundamenta\Components\Auth\Transport\CookieTransport;
use Romanzaycev\Fundamenta\Components\Auth\UserProvider;
use Romanzaycev\Fundamenta\Components\Auth\UserProviderHolder;
use Romanzaycev\Fundamenta\Components\Startup\HookManager;
use Romanzaycev\Fundamenta\Components\Startup\Provisioning\ProvisionDecl;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\ModuleBootstrapper;
use function DI\autowire;
use function DI\get;

class Auth extends ModuleBootstrapper
{
    /** @var TokenStorageSelector[] */
    private static array $selectors = [];

    public static function preconfigure(Configuration $configuration): void
    {
        $configuration->setDefaults(
            "auth",
            [
                "enabled" => false,
                "storage" => null,
                "transports" => [
                    "cookie" => [
                        "name" => "auth_dat",
                        "path" => "/",
                        "domain" => null,
                        "secure" => false,
                        "http_only" => true,
                        "same_site" => "none", // none|lax|strict
                    ],
                    "header" => [
                        "name" => "X-Auth-Token",
                        "format" => "%s",
                    ],
                ],
                "default_transport" => CookieTransport::class,
            ],
            [
                "enabled",
                "transports",
                "default_transport",
            ],
        );
    }

    public static function boot(ContainerBuilder $builder, Configuration $configuration): void
    {
        $builder->addDefinitions([
            TokenStorageHolder::class => autowire(TokenStorageHolder::class),
            TokenTransportHolder::class => autowire(TokenTransportHolder::class),
            UserProviderHolder::class => get(DefaultUserProviderHolder::class),
        ]);
    }

    public static function hooks(HookManager $hookManager): void
    {
        $hookManager->add(
            HookManager::ON_SESSION_STARTED,
            static function (ServerRequestInterface $request, TokenStorageHolder $holder, Container $container): void {
                $holder->addForRequest($request, $container);
            },
        );
        $hookManager->add(
            HookManager::ON_REQUEST_TERMINATED,
            static function (mixed $hookData, TokenStorageHolder $holder): void {
                $holder->terminateAllForRequest();
            }
        );
    }

    public static function provisioning(
        TokenStorageHolder        $storageHolder,
        TokenTransportHolder      $transportHolder,
        DefaultUserProviderHolder $userProviderHolder,
        Container                 $container,
    ): array
    {
        return [
            new ProvisionDecl(
                TokenStorageProvider::class,
                static function (array $providers) use ($storageHolder, $container): void {
                    foreach ($providers as $provider) {
                        /** @var TokenStorageProvider $provider */
                        $cycle = $provider->getLifecycle();

                        switch ($cycle) {
                            case TokenStorageLifecycle::PERSISTENT:
                                $storageHolder->addPersistent($provider->createPersistent($container));
                                break;

                            case TokenStorageLifecycle::PER_REQUEST:
                                $storageHolder->registerForRequestProvider($provider);
                                break;

                            default:
                                throw new \RuntimeException("Unknown life cycle: " . $cycle->name);
                        }
                    }
                },
            ),

            new ProvisionDecl(
                TokenTransportProvider::class,
                static function (array $providers) use ($transportHolder): void {
                    foreach ($providers as $provider) {
                        /** @var TokenTransportProvider $provider */
                        $trs = $provider->get();

                        if (is_array($trs)) {
                            foreach ($trs as $transport) {
                                $transportHolder->register($transport);
                            }
                        } else {
                            $transportHolder->register($trs);
                        }
                    }
                },
            ),

            new ProvisionDecl(
                UserProvider::class,
                static function (array $providers) use ($userProviderHolder): void {
                    /** @var UserProvider[] $providers */
                    foreach ($providers as $provider) {
                        $userProviderHolder->register($provider);
                    }
                },
            ),

            new ProvisionDecl(
                TokenStorageSelector::class,
                static function (array $selectors): void {
                    /** @var TokenStorageSelector[] $selectors */
                    foreach ($selectors as $selector) {
                        self::$selectors[] = $selector;
                    }

                    self::$selectors = array_unique(self::$selectors);
                }
            ),
        ];
    }

    public static function requires(): array
    {
        return [
            TrustedProxy::class,
            Session::class,
        ];
    }

    /**
     * @throws \Exception
     */
    public static function middlewares(
        Configuration $configuration,
        TokenStorageHolder $storageHolder,
        TokenTransportHolder $transportHolder,
    ): array
    {
        if ($configuration->get("auth.enabled", false)) {
            return [
                new AuthedContextMiddleware(
                    $storageHolder,
                    $transportHolder,
                    self::$selectors,
                    $configuration->get("auth.storage"),
                    $configuration->get("auth.default_transport"),
                ),
            ];
        }

        return [];
    }
}
