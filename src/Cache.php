<?php

declare(strict_types=1);

namespace LTS\MarkdownTools;

final class Cache
{
    private string $cacheDir;

    public function __construct(string $cacheDir = null)
    {
        $this->cacheDir = $cacheDir ?? __DIR__ . '/../var/cache';
        if (!is_dir($this->cacheDir)) {
            \Safe\mkdir(pathname: $this->cacheDir, recursive: true);
        }
    }

    public function getCache(string $item): ?string
    {
        $cachePath = $this->getCachePath($item);
        if (file_exists($cachePath)) {
            $contents = \Safe\file_get_contents($cachePath);
            if ($contents !== '') {
                return $contents;
            }
        }

        return null;
    }

    public function setCache(string $item, string $contents): void
    {
        $cachePath = $this->getCachePath($item);
        \Safe\file_put_contents(filename: $cachePath, data: $contents);
    }

    private function getCachePath(string $item): string
    {
        $filename = preg_replace('%\W%', '_', $item);
        if (strlen($filename) > 30) {
            $filename = \Safe\substr($filename, 0, 10) . md5($item);
        }

        return $this->cacheDir . $filename;
    }
}