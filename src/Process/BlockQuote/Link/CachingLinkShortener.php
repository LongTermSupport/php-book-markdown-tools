<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Process\BlockQuote\Link;

use LTS\MarkdownTools\Cache;

final class CachingLinkShortener
{
    private const CACHE_PREFIX = __CLASS__;
    private Cache $cache;

    public function __construct(private ShortenCallableInterface $shortener, Cache $cache = null)
    {
        $this->cache = $cache ?? new Cache();
    }

    public function getShortUrl(string $longUrl): string
    {
        return $this->cache->getCache(prefix: self::CACHE_PREFIX, item: $longUrl) ?? $this->shorten($longUrl);
    }

    private function shorten(string $longUrl): string
    {
        $shortener = $this->shortener;

        $shortUrl = $shortener($longUrl);
        $this->cache->setCache(prefix: self::CACHE_PREFIX, item: $longUrl, contents: $shortUrl);

        return $shortUrl;
    }
}
