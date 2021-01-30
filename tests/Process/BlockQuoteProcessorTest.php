<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test\Process;

use LTS\MarkdownTools\Process\BlockQuote\BlockQuoteProcess;
use LTS\MarkdownTools\Process\BlockQuoteProcessor;
use LTS\MarkdownTools\Test\Util;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \LTS\MarkdownTools\Process\BlockQuoteProcessor
 *
 * @small
 */
final class BlockQuoteProcessorTest extends TestCase
{
    public const TEST_CONTENT = <<<'MARKDOWN'
# Foo Bar 

klha sdkj aksjh ksjh askdjh akjhasd
kjh aksjd hkjasdkjhasd

> blockquote 1 blockquote 1k kjhkjsad
> blockquote 1 blockquote 1 kjhasd
> blockquote 1 blockquote 1kj kjh askjdhasd

kjha sdkjh iuwhri uhsdufh whejkr hsdf
kjh kefruheuf asd ias9uwefdasf

> blockquote 2 blockquote 2k kjhkjsad
> blockquote 2 blockquote  kjhasd
> blockquote 2 blockquote kj kjh askjdhasd
> blockquote 2 blockquote k kjhkjsad
> blockquote 2 blockquote  kjhasd

kjh askdjh kjh weriuh uiowher ujskkkjh kjhkjfd
kjh aksjhdjhasd
MARKDOWN;

    /** @test */
    public function itCanProcessBlockQuotes(): void
    {
        $processor = new BlockQuoteProcessor(
            new class() implements BlockQuoteProcess {
                public function shouldProcess(string $blockquote): bool
                {
                    return str_contains(haystack: $blockquote, needle: 'blockquote 1');
                }

                public function processBlockQuote(string $blockquote): string
                {
                    return "> processed 1 bla\n> processed 1 foo";
                }
            },
            new class() implements BlockQuoteProcess {
                public function shouldProcess(string $blockquote): bool
                {
                    return str_contains(haystack: $blockquote, needle: 'blockquote 2');
                }

                public function processBlockQuote(string $blockquote): string
                {
                    return "> processed 2 foo\n> processed 2 bar";
                }
            }
        );
        $expected  = '# Foo Bar 

klha sdkj aksjh ksjh askdjh akjhasd
kjh aksjd hkjasdkjhasd

> processed 1 bla
> processed 1 foo

kjha sdkjh iuwhri uhsdufh whejkr hsdf
kjh kefruheuf asd ias9uwefdasf

> processed 2 foo
> processed 2 bar

kjh askdjh kjh weriuh uiowher ujskkkjh kjhkjfd
kjh aksjhdjhasd';
        $actual    = $processor->getProcessedContents(self::TEST_CONTENT, Util::VAR_PATH);
        self::assertSame($expected, $actual);
    }
}
