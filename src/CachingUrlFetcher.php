<?php

declare(strict_types=1);

namespace LTS\MarkdownTools;

class CachingUrlFetcher
{
    private const CACHE_PREFIX = __CLASS__;

    public function __construct(string $cacheDir = null)
    {
        $this->cache = new Cache($cacheDir);
    }

    public function getContents(string $url): string
    {
        return $this->cache->getCache(self::CACHE_PREFIX . $url) ?? $this->fetchUrl($url);
    }

    private function fetchUrl(string $url)
    {
        $contents = \Safe\file_get_contents($url);
        $this->cache->setCache(self::CACHE_PREFIX . $url, $contents);

        return $contents;
    }
}