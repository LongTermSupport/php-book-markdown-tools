<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test\MarkdownProcessor\Process\BlockQuote\Link;

use LTS\MarkdownTools\MarkdownProcessor\Process\BlockQuote\Link\CachingLinkShortener;
use LTS\MarkdownTools\MarkdownProcessor\Process\BlockQuote\Link\ShortenCallableInterface;
use LTS\MarkdownTools\Test\TestHelper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \LTS\MarkdownTools\Cache
 * @covers \LTS\MarkdownTools\MarkdownProcessor\Process\BlockQuote\Link\CachingLinkShortener
 *
 * @small
 */
final class CachingLinkShortenerTest extends TestCase
{
    private ShortenCallableInterface $callable;
    private CachingLinkShortener     $shortener;

    protected function setUp(): void
    {
        parent::setUp();
        $this->callable           = new class() implements ShortenCallableInterface {
            public string  $short = 'shortened';
            public ?string $long;

            public function __invoke(string $longUrl): string
            {
                return $this->short;
            }
        };
        $this->shortener = new CachingLinkShortener($this->callable, TestHelper::getCache());
    }

    /** @test */
    public function itCanSetAndGetCache(): void
    {
        /** @phpstan-ignore-next-line */
        $expected = $this->callable->short;
        $actual   = $this->shortener->getShortUrl('foobar');
        self::assertSame($expected, $actual);
    }
}
