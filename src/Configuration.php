<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta;

use Romanzaycev\Fundamenta\Configuration\ConfigurationLoader;

class Configuration
{
    public const ALL = "";

    private bool $loaded = false;

    /**
     * @var array<string, array>
     */
    private array $sections = [];

    /**
     * @var array<string, array>
     */
    private array $defaults = [];

    /**
     * @var array<string, string[]>
     */
    private array $required = [];

    /**
     * @param array<string, array> $initial
     */
    public function __construct(
        private readonly ConfigurationLoader $loader,
        private readonly array $initial = [],
    ) {}

    public function setDefaults(string $section, array $defaults, array $required = []): void
    {
        $this->defaults[$section] = $defaults;
        $this->required[$section] = $required;
    }

    public function get(string $path, $default = null): mixed
    {
        $this->load();

        if ($path === self::ALL) {
            return array_replace_recursive(
                $this->defaults,
                $this->sections,
            );
        }

        $pathItems = explode(".", $path);
        $section = $pathItems[0];
        unset($pathItems[0]);
        $pathItems = array_values($pathItems);

        if (!isset($this->sections[$section]) && !isset($this->defaults[$section])) {
            throw new \InvalidArgumentException("Unknown configuration section: " . $section);
        }

        return $this->getInternal(
            array_replace_recursive(
                $this->defaults[$section] ?? [],
                $this->sections[$section] ?? [],
            ),
            $pathItems,
            $default,
        );
    }

    public function reset(): void
    {
        $this->loaded = false;
        $this->sections = [];
    }

    protected function load(): void
    {
        if ($this->loaded) {
            return;
        }

        $this->sections = array_replace_recursive(
            $this->initial,
            $this->loader->load(),
        );
        $e = [];
        $this->validateRequired(true, $e);
        $this->loaded = true;
    }

    public function validate(): array
    {
        $errors = [];
        $this->validateRequired(false, $errors);

        return $errors;
    }

    protected final function validateRequired(bool $isThrow, array &$errors): void
    {
        foreach ($this->required as $section => $requiredFields) {
            if (empty($requiredFields)) {
                continue;
            }

            foreach ($requiredFields as $requiredField) {
                $sectionData = array_replace_recursive(
                    $this->defaults[$section] ?? [],
                    $this->sections[$section] ?? [],
                );

                if ($this->getInternal($sectionData, $requiredField, "___DEF_VALUE___") === "___DEF_VALUE___") {
                    $error = sprintf(
                        "Invalid \"%s\" section, property \"%s\" is required",
                        $section,
                        $requiredField,
                    );

                    if ($isThrow) {
                        throw new \RuntimeException($error);
                    } else {
                        $errors[] = $error;
                    }
                }
            }
        }
    }

    protected final function getInternal(array $sectionData, string|array $path, mixed $default): mixed
    {
        $pathItems = is_array($path) ? $path : explode(".", $path);
        $previous = $sectionData;

        foreach ($pathItems as $item) {
            if (isset($previous[$item])) {
                $previous = $previous[$item];
            } else {
                return $default;
            }
        }

        return $previous;
    }
}
