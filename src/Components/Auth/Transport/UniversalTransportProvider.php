<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Auth\Transport;

use Romanzaycev\Fundamenta\Components\Auth\TokenTransportProvider;
use Romanzaycev\Fundamenta\Configuration;

readonly class UniversalTransportProvider implements TokenTransportProvider
{
    public function __construct(
        private Configuration $configuration,
    ) {}

    public function get(): HttpTransport|array
    {
        $config = $this->configuration->get("auth.transports");

        return [
            new CookieTransport(
                $config["cookie"]["name"],
                $config["cookie"]["path"],
                $config["cookie"]["domain"],
                $config["cookie"]["secure"],
                $config["cookie"]["http_only"],
                $config["cookie"]["same_site"],
            ),
            new HeaderTransport(
                $config["header"]["name"],
                $config["header"]["format"],
            ),
        ];
    }
}
