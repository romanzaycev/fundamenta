<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Configuration;

readonly class DirectoryLoader implements ConfigurationLoader
{
    public function __construct(
        protected string $directory,
    ) {}

    public function load(): array
    {
        $sections = [];
        $dir = $this->directory;

        if (!\str_ends_with($dir, "/")) {
            $dir .= "/";
        }

        $files = glob($dir . "*.php");

        if (is_array($files)) {
            foreach ($files as $filePath) {
                $filename = basename($filePath);
                $section = strtolower(str_replace(".php", "", $filename));
                $sectionData = include $filePath;

                if (!is_array($sectionData)) {
                    throw new \RuntimeException("Invalid data in " . $filename . ", array expected");
                }

                $sections[$section] = $sectionData;
            }
        }

        return $sections;
    }
}
