<?php

declare(strict_types=1);

namespace MarkdownProcessor\Process\BlockQuote;

use LTS\MarkdownTools\MarkdownProcessor\CachingUrlFetcher;
use LTS\MarkdownTools\MarkdownProcessor\Process\BlockQuote\LinkProcessor;
use LTS\MarkdownTools\Test\TestHelper;
use PHPUnit\Framework\TestCase;

class LinkProcessorTest extends TestCase
{
    private const URL_REGEXP = '%https://.+?%';
    private LinkProcessor $processor;

    public function setUp(): void
    {
        $this->processor = new LinkProcessor(self::URL_REGEXP, new CachingUrlFetcher(TestHelper::getCache()));
    }

    public function provideAlreadyProcessed(): \Generator
    {
        yield 'yes' => [
            '> ###### GitHub - WordPress/wordpress-develop
> https://github.com/WordPress/wordpress-develop',
            true,
        ];
    }

    /**
     * @dataProvider provideAlreadyProcessed
     * @test
     */
    public function itCorrectlyFindsIfAlreadyProcessed(string $blockQuote, bool $expected): void
    {
        self::assertSame($expected, $this->processor->isAlreadyProcessed($blockQuote));
    }
}