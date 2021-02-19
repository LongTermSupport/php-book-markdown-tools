<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test\Process\BlockQuote;

use LTS\MarkdownTools\CachingUrlFetcher;
use LTS\MarkdownTools\Process\BlockQuote\WikipediaLinkProcess;
use LTS\MarkdownTools\Test\TestHelper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \LTS\MarkdownTools\Process\BlockQuote\LinkProcessor
 * @covers \LTS\MarkdownTools\Process\BlockQuote\WikipediaLinkProcess
 *
 * @small
 */
final class WikipediaLinkProcessTest extends TestCase
{
    public const TEST_MARKDOWN = <<<'MARKDOWN'
> kljh kjh asdlhk askjd ajksd 
> https://en.wikipedia.org/w/index.php?title=Data_type&oldid=996603474#:~:text=In%20computer%20science%20and%20computer%20programming,%20a%20data%20type%20or%20simply%20type%20is%20an%20attribute%20of%20data%20which%20tells%20the%20compiler%20or%20interpreter%20how%20the%20programmer%20intends%20to%20use%20the%20data.
> kjhasdkj kajsd
MARKDOWN;

    public const TEST_MARKDOWN_NO_HIGHLIGHT = <<<'MARKDOWN'
> kljh kjh asdlhk askjd ajksd 
> https://en.wikipedia.org/w/index.php?title=Data_type
> kjhasdkj kajsd
MARKDOWN;

    public const TEST_MARKDOWN_WITH_PARENTHESES = <<<'MARKDOWN'
> https://en.wikipedia.org/w/index.php?title=Covariance_and_contravariance_(computer_science)&oldid=1001839343#:~:text=In%20the%20O
MARKDOWN;

    private WikipediaLinkProcess $process;

    public function setUp(): void
    {
        parent::setUp();
        $this->process = new WikipediaLinkProcess(
            urlFetcher: new CachingUrlFetcher(TestHelper::getCache()),
            cache: TestHelper::getCache()
        );
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
                self::TEST_MARKDOWN_NO_HIGHLIGHT,
                true,
            ],
            [
                '> https://en.wikipedia.org/wiki/Data_type#:~:text=In%20computer%20science%20and%20computer%20programming,%20a%20data%20type%20or%20simply%20type%20is%20an%20attribute%20of%20data%20which%20tells%20the%20compiler%20or%20interpreter%20how%20the%20programmer%20intends%20to%20use%20the%20data.',
                true,
            ],
            [
                '> https://en.wikipedia.org/wiki/Data_type',
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
        $actual   = $this->process->processBlockQuote(self::TEST_MARKDOWN_NO_HIGHLIGHT);
        $expected = '> ###### Data type - Wikipedia
> [https://w.wiki/wor](https://en.wikipedia.org/w/index.php?title=Data_type)';
        self::assertSame($expected, $actual);
    }

    /** @test */
    public function itCanBuildDocsLinkBlocksWithTextHighlight(): void
    {
        $actual   = $this->process->processBlockQuote(self::TEST_MARKDOWN);
        $expected = '> ###### Data type - Wikipedia
> [https://w.wiki/wow](https://en.wikipedia.org/w/index.php?title=Data_type&oldid=996603474#:~:text=In%20computer%20science%20and%20computer%20programming,%20a%20data%20type%20or%20simply%20type%20is%20an%20attribute%20of%20data%20which%20tells%20the%20compiler%20or%20interpreter%20how%20the%20programmer%20intends%20to%20use%20the%20data.)
> In computer science and computer
> programming, a data type or simply type
> is an attribute of data which tells the
> compiler or interpreter how the
> programmer intends to use the data.)';
        self::assertSame($expected, $actual);
    }

    /** @test */
    public function itCanHandleParentheses(): void
    {
        $actual   = $this->process->processBlockQuote(self::TEST_MARKDOWN_WITH_PARENTHESES);
        $expected = '> ###### Covariance and contravariance (computer science) - Wikipedia
> [https://w.wiki/xGg](https://en.wikipedia.org/w/index.php?title=Covariance_and_contravariance_(computer_science)&oldid=1001839343#:~:text=In%20the%20O)
> In the O)';
        self::assertSame($expected, $actual);
    }
}
