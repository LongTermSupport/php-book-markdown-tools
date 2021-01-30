<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test\Process\BlockQuote\Link;

use LTS\MarkdownTools\Cache;
use LTS\MarkdownTools\Process\BlockQuote\Link\CachingLinkShortener;
use LTS\MarkdownTools\Process\BlockQuote\Link\ShortenCallableInterface;
use LTS\MarkdownTools\Test\Util;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \LTS\MarkdownTools\Process\BlockQuote\Link\CachingLinkShortener
 * @covers \LTS\MarkdownTools\Cache
 */
class CachingLinkShortenerTest extends TestCase
{
    private Cache                    $cache;
    private ShortenCallableInterface $callable;
    private CachingLinkShortener     $shortener;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache     = new Cache(Util::CACHE_PATH);
        $this->callable  = new class implements ShortenCallableInterface {
            public string  $short = 'shortened';
            public ?string $long;

            public function __invoke(string $longUrl): string
            {
                return $this->short;
            }
        };
        $this->shortener = new CachingLinkShortener($this->callable);
    }

    /** @test */
    public function itCanSetAndGetCache(): void
    {
        $expected = $this->callable->short;
        $actual   = $this->shortener->getShortUrl('foobar');
        self::assertSame($expected, $actual);
    }
}