<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test\MarkdownProcessor\Process\BlockQuote;

use LTS\MarkdownTools\MarkdownProcessor\CachingUrlFetcher;
use LTS\MarkdownTools\MarkdownProcessor\Process\BlockQuote\GithubLinkProcess;
use LTS\MarkdownTools\Test\TestHelper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \LTS\MarkdownTools\MarkdownProcessor\Process\BlockQuote\LinkProcessor
 * @covers \LTS\MarkdownTools\MarkdownProcessor\Process\BlockQuote\GithubLinkProcess
 * @covers \LTS\MarkdownTools\Util\LinkShortener\GithubLinkShortener
 *
 * @small
 */
final class GithubLinkProcessTest extends TestCase
{
    public const TEST_MARKDOWN = <<<'MARKDOWN'
    > https://github.com/prooph/proophessor-do
    MARKDOWN;

    public const TEST_MARKDOWN_ALREADY_PROCESSED = <<<'MARKDOWN'
    > ###### GitHub - prooph/proophessor-do: prooph components in action
    > [https://git.io/JYxbv](https://github.com/prooph/proophessor-do)
    MARKDOWN;

    public const TEST_MARKDOWN_ALREADY_PROCESSED_BUT_NOT_SHORTEND= <<<'MARDOWN'
    > ###### symfony/AllMySmsTransportFactory.php at ffc2c1e1dacccf57848d1a63d92fc6fd48250bc1 · symfony/symfony · GitHub
    > https://github.com/symfony/symfony/blob/ffc2c1e1dacccf57848d1a63d92fc6fd48250bc1/src/Symfony/Component/Notifier/Bridge/AllMySms/AllMySmsTransportFactory.php
    MARDOWN;



    private GithubLinkProcess $process;

    public function setUp(): void
    {
        parent::setUp();
        $this->process = new GithubLinkProcess(
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
                null,
            ],
            [
                self::TEST_MARKDOWN,
                true,
                self::TEST_MARKDOWN_ALREADY_PROCESSED,
            ],
            [
                self::TEST_MARKDOWN_ALREADY_PROCESSED,
                false,
                null,
            ],
        ];
    }

    /**
     * @dataProvider provideShouldProcess
     * @test
     */
    public function shouldProcess(string $block, bool $expectedShouldProcess, ?string $expectedOutput): void
    {
        $actualShouldProcess = $this->process->shouldProcess($block);
        self::assertSame($expectedShouldProcess, $actualShouldProcess);
        if (false === $actualShouldProcess) {
            return;
        }
        $actualOutput = $this->process->processBlockQuote($block);
        self::assertSame($expectedOutput, $actualOutput);
    }
}
