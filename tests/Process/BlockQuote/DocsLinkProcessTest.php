<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test\Process\BlockQuote;

use LTS\MarkdownTools\CachingUrlFetcher;
use LTS\MarkdownTools\Process\BlockQuote\DocsLinkProcess;
use LTS\MarkdownTools\Test\TestHelper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \LTS\MarkdownTools\Process\BlockQuote\LinkProcessor
 * @covers \LTS\MarkdownTools\Process\BlockQuote\WikipediaLinkProcess
 *
 * @small
 */
final class DocsLinkProcessTest extends TestCase
{
    public const TEST_MARKDOWN = <<<'MARKDOWN'
> kljh kjh asdlhk askjd ajksd 
> https://www.php.net/manual/en/language.types.boolean.php
> kjhasdkj kajsd
MARKDOWN;
    private DocsLinkProcess $process;

    public function setUp(): void
    {
        parent::setUp();
        $this->process = new DocsLinkProcess(new CachingUrlFetcher(TestHelper::getCache()));
    }

    /** @dataProvider
     * @return array<mixed>
     */
    public function provideShouldProcess(): array
    {
        return [
            [
                <<<'MARKDOWN'
> kjasdkjhaskjdh akhd askdhjasd
> kjha skdjh akjhdadasdasd
MARKDOWN,
                false,
            ],
            [
                self::TEST_MARKDOWN,
                true,
            ],
            [
                '> https://www.php.net/manual/en/language.types.boolean.php',
                true,
            ],
        ];
    }

    /**
     * @dataProvider provideShouldProcess
     * @test
     */
    public function shouldProcess(string $block, bool $expected): void
    {
        $actual = $this->process->shouldProcess($block);
        self::assertSame($expected, $actual);
    }

    /** @test */
    public function itCanBuildDocsLinkBlocks(): void
    {
        $actual   = $this->process->processBlockQuote(self::TEST_MARKDOWN);
        $expected = '> #### PHP: Booleans - Manual 
> https://www.php.net/manual/en/language.types.boolean.php';
        self::assertSame($expected, $actual);
    }
}
