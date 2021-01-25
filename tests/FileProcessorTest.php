<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test;

use LTS\MarkdownTools\FileProcessor;
use LTS\MarkdownTools\ProcessorInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \LTS\MarkdownTools\FileProcessor
 *
 * @small
 */
final class FileProcessorTest extends TestCase
{
    const EXPECTED = 'processed';

    protected function setUp(): void
    {
        parent::setUp();
        Util::nuke();
    }

    /** @test */
    public function itCanProcessFiles(): void
    {
        $filePath = Util::createTestFile('unprocessed');
        self::getProcessor()->processFile($filePath);
        self::assertSame(self::EXPECTED, \Safe\file_get_contents($filePath));
    }

    public static function getProcessor(): FileProcessor
    {
        return new FileProcessor(new class() implements ProcessorInterface {
            public function getProcessedContents(string $currentContents, string $currentFileDir): string
            {
                return FileProcessorTest::EXPECTED;
            }
        });
    }
}
