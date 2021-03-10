<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\MarkdownProcessor;

use LTS\MarkdownTools\Cache;
use LTS\MarkdownTools\Util\Curl;

final class CachingUrlFetcher
{
    private const CACHE_PREFIX = __CLASS__;

    public function __construct(private ?Cache $cache = null, private ?Curl $curl = null)
    {
        $this->cache ??= new Cache();
        $this->curl  ??= new Curl();
    }

    public function getContents(string $url): string
    {
        return $this->cache->getCache(prefix: self::CACHE_PREFIX, item: $url) ?? $this->fetchUrl($url);
    }

    private function fetchUrl(string $url): string
    {
        $contents = $this->curl->fetchUrl($url);
        $this->cache->setCache(prefix: self::CACHE_PREFIX, item: $url, contents: $contents);

        return $contents;
    }
}
