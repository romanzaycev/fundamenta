<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Http\Static;

interface InternalStaticFileInterface
{
    public function getPublicFile(): string;

    public function getRealFile(): string;

    public function preprocess(string $content): ?string;

    public function isPreprocessed(): bool;

    public function isVirtualRewrite(): bool;
}
