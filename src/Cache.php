<?php

declare(strict_types=1);

namespace LTS\MarkdownTools;

use RuntimeException;

final class Cache
{
    public const MIN_CACHE_DIR_PATH_LENGTH = 10;
    private string $cacheDir;

    public function __construct(string $cacheDir = null)
    {
        $this->cacheDir = $cacheDir ?? __DIR__ . '/../var/cache';
        $this->cacheDir .= '/';
        $this->assertCacheDirExists();
    }

    public function getCache(string $prefix, string $item): ?string
    {
        $cachePath = $this->getCachePath($prefix, $item);
        if (file_exists($cachePath)) {
            $contents = \Safe\file_get_contents($cachePath);
            if ($contents !== '') {
                return $contents;
            }
        }

        return null;
    }

    public function setCache(string $prefix, string $item, string $contents): void
    {
        $cachePath = $this->getCachePath($prefix, $item);
        \Safe\file_put_contents(filename: $cachePath, data: $contents);
    }

    private function assertCacheDirExists(): void
    {
        if (strlen($this->cacheDir) < self::MIN_CACHE_DIR_PATH_LENGTH) {
            throw new RuntimeException('CacheDir is very short and seems incorect: ' . $this->cacheDir);
        }
        if (!is_dir($this->cacheDir)) {
            \Safe\mkdir(pathname: $this->cacheDir, recursive: true);
        }
        $this->cacheDir=\Safe\realpath($this->cacheDir) . '/';
    }

    private function getCachePath(string $prefix, string $item): string
    {
        $this->assertCacheDirExists();
        /** @var string $prefix */
        $prefix = \Safe\preg_replace('%\W%', '_', $prefix);
        /** @var string $suffix */
        $suffix = \Safe\preg_replace('%\W%', '_', $item);
        if (strlen($suffix) > 100) {
            $suffix = \Safe\substr($suffix, 0, 100) . md5($item);
        }
        $cacheFileName = "{$prefix}|{$suffix}.cache";

        return $this->cacheDir . '/' . $cacheFileName;
    }
}
