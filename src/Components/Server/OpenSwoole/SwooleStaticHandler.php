<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Server\OpenSwoole;

use Psr\Log\LoggerInterface;
use Romanzaycev\Fundamenta\Components\Http\Static\InternalStaticDirectoryInterface;
use Romanzaycev\Fundamenta\Components\Http\Static\InternalStaticFileInterface;
use Romanzaycev\Fundamenta\Components\Http\Static\StaticHandler;
use OpenSwoole\HTTP\Response;

class SwooleStaticHandler implements StaticHandler
{
    /** @var InternalStaticFileInterface[] */
    private array $files = [];

    /** @var InternalStaticDirectoryInterface[] */
    private array $directories = [];

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function add(InternalStaticFileInterface|InternalStaticDirectoryInterface $entry): void
    {
        if ($entry instanceof InternalStaticFileInterface) {
            $this->files[$this->normalizePath($entry->getPublicFile())] = $entry;
            return;
        }

        $this->directories[$this->normalizePath($entry->getPublicDirectory())] = $entry;
    }

    public function tryRespond(string $requestPath, Response $response): bool
    {
        try {
            $requestPath = $this->normalizePath($requestPath);

            if (str_ends_with($requestPath, ".php")) {
                return false;
            }

            if (isset($this->files[$requestPath])) {
                $this->respondFile($this->files[$requestPath], $response);

                return true;
            }

            foreach ($this->directories as $probRequestedDir => $directory) {
                if (\str_starts_with($requestPath, $probRequestedDir)) {
                    $this->respondFileFromDirectory(
                        $directory,
                        mb_substr($requestPath, mb_strlen($probRequestedDir)),
                        $response,
                    );

                    return true;
                }
            }

            if ($file = $this->isVirtualRewriteMatched($requestPath)) {
                $this->respondFile($file, $response);

                return true;
            }
        } catch (\Throwable $e) {
            $this
                ->logger
                ->error(
                    "[SwooleStaticHandler] TryRespond error:" . $e->getMessage(),
                    [
                        "exception" => $e,
                        "requestPath" => $requestPath,
                    ]
                );
        }

        return false;
    }

    private function normalizePath(string $path): string
    {
        $path = trim($path);

        if (!\str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        return rtrim($path, '/');
    }

    private function respondFile(InternalStaticFileInterface $file, Response $response): void
    {
        $realPath = realpath($file->getRealFile());

        if (!$realPath || !is_file($realPath)) {
            $response->status(404);
            $response->end();
            return;
        }

        if (!$file->isPreprocessed()) {
            $this->sendfile($realPath, $response);
            return;
        }

        $response->end($file->preprocess(file_get_contents($realPath)));
    }

    private function respondFileFromDirectory(
        InternalStaticDirectoryInterface $dir,
        string $requestedFile,
        Response $response,
    ): void
    {
        $this->sendfile(realpath($dir->getRealDirectory() . "/" . $requestedFile), $response);
    }

    private function sendfile(string|false $realPath, Response $response): void
    {
        if (!$realPath || !is_file($realPath)) {
            $response->status(404);
            $response->end();
            return;
        }

        $response->sendfile($realPath);
    }

    private function isVirtualRewriteMatched(string $requestPath): ?InternalStaticFileInterface
    {
        foreach ($this->files as $file) {
            if ($file->isVirtualRewrite()) {
                $filePubPath = $file->getPublicFile();
                $filePubPath .= "/";

                if (\str_starts_with($requestPath, $filePubPath)) {
                    return $file;
                }
            }
        }

        return null;
    }
}
