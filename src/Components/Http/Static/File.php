<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Http\Static;

class File implements InternalStaticFileInterface
{
    /**
     * @var (callable(string, File): string)|null
     */
    private $preprocessor = null;

    public function __construct(
        private readonly string $public,
        private readonly string $real,
        ?callable $preprocessor = null,
    )
    {
        $this->preprocessor = $preprocessor;
    }

    public function getPublicFile(): string
    {
        return $this->public;
    }

    public function getRealFile(): string
    {
        return $this->real;
    }

    public function preprocess(string $content): ?string
    {
        if ($this->isPreprocessed()) {
            return call_user_func($this->preprocessor, $content, $this);
        }

        return null;
    }

    public function isPreprocessed(): bool
    {
        return $this->preprocessor !== null;
    }

    /**
     * @param (callable(string, File): string)|null $preprocessor
     * @return File
     */
    public function setPreprocessor(?callable $preprocessor): self
    {
        $this->preprocessor = $preprocessor;

        return $this;
    }
}
