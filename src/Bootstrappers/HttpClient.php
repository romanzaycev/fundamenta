<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Bootstrappers;

use DI\ContainerBuilder;
use Http\Client\Common\HttpMethodsClient;
use Http\Client\Common\HttpMethodsClientInterface;
use Http\Client\Curl\Client;
use Http\Discovery\Psr17FactoryDiscovery;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\ModuleBootstrapper;

class HttpClient extends ModuleBootstrapper
{
    public static function boot(ContainerBuilder $builder, Configuration $configuration): void
    {
        $builder->addDefinitions([
            HttpMethodsClientInterface::class => static function () {
                $requestFactory = Psr17FactoryDiscovery::findRequestFactory();
                $streamFactory = Psr17FactoryDiscovery::findStreamFactory();

                return new HttpMethodsClient(
                    new Client(
                        Psr17FactoryDiscovery::findResponseFactory(),
                        $streamFactory,
                    ),
                    $requestFactory,
                    $streamFactory,
                );
            },
        ]);
    }
}
