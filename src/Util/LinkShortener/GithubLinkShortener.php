<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Util\LinkShortener;

use CurlHandle;
use InvalidArgumentException;
use LTS\MarkdownTools\Cache;
use RuntimeException;

final class GithubLinkShortener implements LinkShortenerInterface
{
    public const URL_REGEXP = '%^https://github.com/%';
    private CachingLinkShortener $cachingLinkShortener;

    public function __construct(Cache $cache = null)
    {
        $this->cachingLinkShortener = new CachingLinkShortener($this->getCallable(), $cache);
    }

    public function canShorten(string $longUrl): bool
    {
        return \Safe\preg_match(self::URL_REGEXP, $longUrl) === 1;
    }

    public function getShortenedLinkMarkDown(string $longUrl): string
    {
        $this->assertValidUrl($longUrl);
        $shortUrl     = $this->cachingLinkShortener->getShortUrl($longUrl);
        $relativePath = $this->getRelativePathFromGithubUrl($longUrl);

        return "[{$shortUrl}]({$longUrl}) {$relativePath}";
    }

    private function assertValidUrl(string $longUrl): void
    {
        if ($this->canShorten($longUrl)) {
            return;
        }
        throw new InvalidArgumentException('Invalid github url: ' . $longUrl);
    }

    private function getRelativePathFromGithubUrl(string $githubUrl): string
    {
        $pattern = '%https://github.com/[^/]+?/[^/]+?/[^/]+?/[^/]+?/(?<relative_path>.+)%';
        \Safe\preg_match($pattern, $githubUrl, $matches);

        return $matches['relative_path'] ?? throw new RuntimeException('Failed finding relative path');
    }

    private function getCallable(): ShortenCallableInterface
    {
        return new class() implements ShortenCallableInterface {
            private const URL_BASE   = 'https://git.io/';
            private const URL_CREATE = self::URL_BASE . 'create';
            private static ?CurlHandle $curl;

            public function __invoke(string $longUrl): string
            {
                return $this->shorten($longUrl);
            }

            private function shorten(string $githubUrl): string
            {
                $curl = $this->getCurl();
                \Safe\curl_setopt($curl, CURLOPT_POSTFIELDS, 'url=' . urlencode($githubUrl));
                $result = (string)\Safe\curl_exec($curl);
                $length = strlen($result);
                if ($length < 5 || $length > 10) {
                    throw new RuntimeException('unexpected short URL fragment: ' . $result);
                }

                return self::URL_BASE . $result;
            }

            private function getCurl(): CurlHandle
            {
                return static::$curl ??= (static function (): CurlHandle {
                    $curl = curl_init();
                    \Safe\curl_setopt($curl, CURLOPT_URL, self::URL_CREATE);
                    \Safe\curl_setopt($curl, CURLOPT_POST, true);
                    \Safe\curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                    return $curl;
                })();
            }
        };
    }
}
