<?php

namespace Romanzaycev\Fundamenta\Components\Views;

use Nyholm\Psr7\Stream;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Romanzaycev\Fundamenta\Components\Views\Cache\AutokeyStrategy;
use Romanzaycev\Fundamenta\Components\Views\Cache\DataIterator;
use Romanzaycev\Fundamenta\Components\Views\Engines\NullEngine;
use Romanzaycev\Fundamenta\Components\Views\Exceptions\EngineManagerException;
use Romanzaycev\Fundamenta\Components\Views\Exceptions\RenderingException;
use Romanzaycev\Fundamenta\Configuration;

class ViewManager implements View, EngineManager
{
    private readonly ViewEngine $nullEngine;

    /**
     * @var array<string, ViewEngine>
     */
    protected array $engines = [];

    private readonly bool $isCacheEnabled;
    private int $cacheTtl;
    private AutokeyStrategy $cacheAutokeyStrategy;

    public function __construct(
        private readonly Configuration $configuration,
        private readonly CacheItemPoolInterface $cache,
        private readonly LoggerInterface $logger,
    )
    {
        $this->nullEngine = new NullEngine();
        $this->isCacheEnabled = (bool)$this->configuration->get("views.cache.enabled", false);
        $this->cacheTtl = (int)$this->configuration->get("views.cache.ttl_seconds", 300);
        $this->cacheAutokeyStrategy = AutokeyStrategy::from(
            $this->configuration->get("views.cache.autokey", "none"),
        );
    }

    /**
     * @throws EngineManagerException
     */
    public function register(string $fileExtension, ViewEngine $engine): void
    {
        if (isset($this->engines[$fileExtension])) {
            throw new EngineManagerException(sprintf(
                "Engine for file file extension \"%s\" already registered",
                $fileExtension,
            ));
        }

        $this->engines[$fileExtension] = $engine;
    }

    public function getEngine(string $fileExtension): ViewEngine
    {
        return $this->engines[$fileExtension] ?? $this->nullEngine;
    }

    /**
     * @throws InvalidArgumentException
     * @throws RenderingException
     */
    public function render(string $templatePath, array $data, array $options = []): string
    {
        $cacheKey = $options["cache_key"] ?? null;
        $forceSkipCache = $options["cache_skip"] ?? false;

        if (!$forceSkipCache && $this->isCacheEnabled) {
            if (is_null($cacheKey) && $this->cacheAutokeyStrategy !== AutokeyStrategy::NONE) {
                $cacheKey = "views_" . $this->createCacheKey($templatePath, $data);
                $item = $this->cache->getItem($cacheKey);

                if ($item->isHit()) {
                    return $item->get();
                }
            }
        }

        $result = $this->renderInternal($templatePath, $data);

        if ($result) {
            if (!$forceSkipCache && $this->isCacheEnabled) {
                if (is_null($cacheKey)) {
                    if ($this->cacheAutokeyStrategy !== AutokeyStrategy::NONE) {
                        $cacheKey = $this->createCacheKey($templatePath, $data);
                    } else {
                        $this
                            ->logger
                            ->notice(sprintf(
                                "[ViewSystem] Rendering without cache key, template \"%s\", skipped",
                                $templatePath,
                            ));

                        return $result;
                    }
                }

                $this
                    ->cache
                    ->save(
                        $this
                            ->cache
                            ->getItem($cacheKey)
                            ->set($result)
                            ->expiresAfter($this->cacheTtl),
                    );
            }

            return $result;
        }

        return $this->nullEngine->render($templatePath, $data);
    }

    /**
     * @throws InvalidArgumentException
     * @throws RenderingException
     */
    public function renderStream(string $templatePath, array $data, array $options = []): StreamInterface
    {
        return Stream::create($this->render($templatePath, $data, $options));
    }

    /**
     * @throws Exceptions\RenderingException
     */
    protected function renderInternal(string $templatePath, array &$data): ?string
    {
        foreach ($this->engines as $ext => $_) {
            if (str_ends_with($templatePath, $ext)) {
                return $this
                    ->getEngine($ext)
                    ->render($templatePath, $data);
            }
        }

        return null;
    }

    protected function createCacheKey(string $templatePath, array &$data): string
    {
        if ($this->cacheAutokeyStrategy === AutokeyStrategy::TEMPLATE_NAME) {
            return md5($templatePath);
        }

        if ($this->cacheAutokeyStrategy === AutokeyStrategy::TEMPLATE_NAME_AND_DATA) {
            $maxDepth = 3;
            $maxItems = 100;
            $s = [];
            $i = 0;

            foreach (new DataIterator($data, $maxDepth) as $k => $v) {
                if ($i >= $maxItems) {
                    break;
                }

                $i++;
                $s[(string)$k] = (string)$v;
            }

            return md5($templatePath . serialize($s));
        }

        throw new \RuntimeException();
    }
}
