<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Http\Static;

class File implements InternalStaticFileInterface
{
    /**
     * @var callable|null
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
            return call_user_func($this->preprocessor, $this, $content);
        }

        return null;
    }

    public function isPreprocessed(): bool
    {
        return $this->preprocessor !== null;
    }

    public function setPreprocessor(?callable $preprocessor): void
    {
        $this->preprocessor = $preprocessor;
    }
}
