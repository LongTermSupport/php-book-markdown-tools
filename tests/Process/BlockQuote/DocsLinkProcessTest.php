<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test\Process\BlockQuote;

use LTS\MarkdownTools\Process\BlockQuote\DocsLinkProcess;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
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
                <<<'MARKDOWN'
> kjasdkjhaskjdh akhd askdhjasd
> https://www.php.net/manual/en/language.types.boolean.php
> kjha skdjh akjhdadasdasd
MARKDOWN,
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
        $actual = (new DocsLinkProcess())->shouldProcess($block);
        self::assertSame($expected, $actual);
    }

    /** @test */
    public function itCanBuildDocsLinkBlocks(): void
    {
        $actual   = (new DocsLinkProcess())->processBlockQuote(self::TEST_MARKDOWN);
        $expected = '> PHP: Booleans - Manual 
> https://www.php.net/manual/en/language.types.boolean.php
>  * Booleans
>   * Syntax
>   * Converting to boolean
';
        self::assertSame($expected, $actual);
    }
}
