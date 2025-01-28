<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Auth;

use Psr\Http\Message\ServerRequestInterface;

interface TokenStorageSelector
{
    /**
     * @param class-string<TokenStorage>[] $storages
     * @return class-string<TokenStorage>|null
     */
    public function select(ServerRequestInterface $request, array $storages): ?string;
}
