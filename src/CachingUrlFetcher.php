<?php

declare(strict_types=1);

namespace LTS\MarkdownTools;

final class CachingUrlFetcher
{
    private const CACHE_PREFIX = __CLASS__;
    private Cache $cache;

    public function __construct(?Cache $cache = null)
    {
        $this->cache = $cache ?? new Cache();
    }

    public function getContents(string $url): string
    {
        return $this->cache->getCache(prefix: self::CACHE_PREFIX, item: $url) ?? $this->fetchUrl($url);
    }

    private function fetchUrl(string $url): string
    {
        $contents = \Safe\file_get_contents($url);
        $this->cache->setCache(prefix: self::CACHE_PREFIX, item: $url, contents: $contents);

        return $contents;
    }

    private function ensureEncode(string $fragment): string
    {
        return urlencode(urldecode($fragment));
    }
}
