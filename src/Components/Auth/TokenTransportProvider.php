<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Auth;

use Romanzaycev\Fundamenta\Components\Auth\Transport\HttpTransport;

interface TokenTransportProvider
{
    /**
     * @return HttpTransport|HttpTransport[]
     */
    public function get(): HttpTransport|array;
}
