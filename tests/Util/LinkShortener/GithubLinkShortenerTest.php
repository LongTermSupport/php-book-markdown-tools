<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test\Util\LinkShortener;

use LTS\MarkdownTools\Test\TestHelper;
use LTS\MarkdownTools\Util\LinkShortener\GithubLinkShortener;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \LTS\MarkdownTools\Util\LinkShortener\GithubLinkShortener
 *
 * @small
 */
final class GithubLinkShortenerTest extends TestCase
{
    private const LONG         = 'https://github.com/LongTermSupport/php-book-code/blob/master/composer.json';
    private const SHORT_PREFIX = '[https://git.io/';
    private GithubLinkShortener $shortener;

    protected function setUp(): void
    {
        parent::setUp();
        $this->shortener = new GithubLinkShortener(TestHelper::getCache());
    }

    /** @test */
    public function itCanShorten(): void
    {
        $actual = $this->shortener->getShortenedLinkMarkDown(self::LONG);
        self::assertStringStartsWith(self::SHORT_PREFIX, $actual);
    }
}
