<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Http\Static;

interface StaticHandler
{
    public function add(InternalStaticFileInterface|InternalStaticDirectoryInterface $entry): void;
}
