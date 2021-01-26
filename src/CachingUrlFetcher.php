<?php

declare(strict_types=1);

namespace LTS\MarkdownTools;

class CachingUrlFetcher
{
    public function __construct(private string $cacheDir)
    {
        if (!is_dir($this->cacheDir)) {
            \Safe\mkdir(pathname: $this->cacheDir, recursive: true);
        }
    }

    public function getContents(string $url): string
    {
        return $this->getCache($url) ?? $this->fetchUrl($url);
    }


    private function getCache(string $url): ?string
    {
        $cachePath = $this->getCachePath($url);
        if (file_exists($cachePath)) {
            $contents = \Safe\file_get_contents($cachePath);
            if (strlen($contents) > 100) {
                return $contents;
            }
        }

        return null;
    }

    private function getCachePath(string $url): string
    {
        $filename = preg_replace('%\W%', '_', $url);

        return $this->cacheDir . $filename;
    }

    private function fetchUrl(string $url)
    {
        $contents  = \Safe\file_get_contents($url);
        $cachePath = $this->getCachePath($url);
        \Safe\file_put_contents(filename: $cachePath, data: $contents);

        return $contents;
    }
}