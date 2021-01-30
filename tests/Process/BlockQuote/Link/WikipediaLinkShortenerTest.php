<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test\Process\BlockQuote\Link;

use LTS\MarkdownTools\Process\BlockQuote\Link\WikipediaLinkShortener;
use LTS\MarkdownTools\Test\TestHelper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 *
 * @small
 */
final class WikipediaLinkShortenerTest extends TestCase
{
    private const LONG         = 'https://en.wikipedia.org/wiki/Data_type#:~:text=In%20computer%20science%20and%20computer%20programming,%20a%20data%20type%20or%20simply%20type%20is%20an%20attribute%20of%20data%20which%20tells%20the%20compiler%20or%20interpreter%20how%20the%20programmer%20intends%20to%20use%20the%20data.';
    private const SHORT_PREFIX = '[https://w.wiki/';
    private WikipediaLinkShortener $shortener;

    protected function setUp(): void
    {
        parent::setUp();
        $this->shortener = new WikipediaLinkShortener(TestHelper::getCache());
    }

    /** @test */
    public function itCanShorten(): void
    {
        $actual = $this->shortener->getShortenedLinkMarkDown(self::LONG);
        self::assertStringStartsWith(self::SHORT_PREFIX, $actual);
    }
}
