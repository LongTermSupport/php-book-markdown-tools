<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Util\LinkShortener;

use InvalidArgumentException;
use LTS\MarkdownTools\Cache;
use RuntimeException;
use Throwable;

final class WikipediaLinkShortener implements LinkShortenerInterface
{
    /** Copy from Chrome Dev Tools */
    public const CURL_CMD = <<<'CMDLINE'
curl 'https://meta.wikimedia.org/w/api.php' \
-H 'authority: meta.wikimedia.org' \
-H 'pragma: no-cache' \
-H 'cache-control: no-cache' \
-H 'accept: application/json, text/javascript, */*; q=0.01' \
-H 'x-requested-with: XMLHttpRequest' \
-H 'user-agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.96 Safari/537.36' \
-H 'content-type: application/x-www-form-urlencoded; charset=UTF-8' \
-H 'origin: https://meta.wikimedia.org' \
-H 'sec-fetch-site: same-origin' \
-H 'sec-fetch-mode: cors' \
-H 'sec-fetch-dest: empty' \
-H 'referer: https://meta.wikimedia.org/wiki/Special:UrlShortener' \
-H 'accept-language: en-GB,en-US;q=0.9,en;q=0.8' \
-H 'cookie: WMF-Last-Access=30-Jan-2021; GeoIP=GB:ENG:Bradford:53.79:-1.76:v4; metawikiwmE-sessionTickLastTickTime=1612018869497; metawikiwmE-sessionTickTickCount=27' \
--data-raw 'action=shortenurl&format=json&url=%s' \
--compressed 2>&1
CMDLINE;
    private const URL_REGEXP = <<<'REGEXP'
%^.*?\.(wikipedia|wiktionary|wikibooks|wikinews|wikiquote|wikisource|wikiversity|wikivoyage|wikimedia|wikidata.org|mediawiki)\.org%
REGEXP;

    private CachingLinkShortener $cachingLinkShortener;

    public function __construct(Cache $cache = null)
    {
        $this->cachingLinkShortener = new CachingLinkShortener($this->getCallable(), $cache);
    }

    public function getShortenedLinkMarkDown(string $longUrl): string
    {
        $this->assertValidUrl($longUrl);
        $shortUrl = $this->cachingLinkShortener->getShortUrl($longUrl);

        return "[{$shortUrl}]({$longUrl})";
    }

    /**
     * *.wikipedia|wiktionary|wikibooks|wikinews|wikiquote|wikisource.org,
     * *.wikiversity|wikivoyage|wikimedia|wikidata.org and *.mediawiki.org.
     */
    public function canShorten(string $longUrl): bool
    {
        return \Safe\preg_match(self::URL_REGEXP, $longUrl) === 1;
    }

    private function assertValidUrl(string $longUrl): void
    {
        if ($this->canShorten($longUrl) === true) {
            return;
        }
        throw new InvalidArgumentException('Invalid wikimedia url: ' . $longUrl);
    }

    private function getCallable(): ShortenCallableInterface
    {
        return new class() implements ShortenCallableInterface {
            public function __invoke(string $longUrl): string
            {
                $cmd = sprintf(WikipediaLinkShortener::CURL_CMD, urlencode($longUrl));
                exec($cmd, $output, $exitCode);
                $output = implode("\n", $output);
                if ($exitCode !== 0) {
                    throw new RuntimeException("Failed getting wikipedia short URL, command output:\n{$output}");
                }

                try {
                    $json = strstr(haystack: $output, needle: '{');
                    if ($json === false) {
                        throw new RuntimeException("Output is not json:\n{$output}");
                    }
                    $decoded = \Safe\json_decode(json: $json, assoc: true);

                    return $decoded['shortenurl']['shorturl'];
                } catch (Throwable $throwable) {
                    throw new RuntimeException("Failed parsing short URL response, raw output:\n{$output}");
                }
            }
        };
    }
}
