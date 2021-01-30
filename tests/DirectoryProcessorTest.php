<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test;

use LTS\MarkdownTools\DirectoryProcessor;
use LTS\MarkdownTools\RunConfig;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \LTS\MarkdownTools\DirectoryProcessor
 *
 * @small
 */
final class DirectoryProcessorTest extends TestCase
{
    public const TEST_DIR = Util::VAR_PATH . 'DirectoryProcessorTest';
    public const EXPECTED = [
        '/DirectoryProcessorTest/Chapter4/file4.txt' => 'processed',
        '/DirectoryProcessorTest/Chapter1/file1.txt' => 'processed',
        '/DirectoryProcessorTest/Chapter5/file5.txt' => 'processed',
        '/DirectoryProcessorTest/Chapter2/file2.txt' => 'processed',
        '/DirectoryProcessorTest/Chapter3/file3.txt' => 'processed',
    ];

    public function setUp(): void
    {
        parent::setUp();
        Util::nuke();
        Util::createVarDir(self::TEST_DIR);
    }

    /** @test */
    public function itCanProcessADirectory(): void
    {
        foreach (range(1, 5) as $i) {
            Util::createTestFile(
                contents: 'contents' . $i,
                filename: 'file' . $i . '.txt',
                createInDir: self::TEST_DIR . '/Chapter' . $i
            );
        }
        $config = new RunConfig(pathToChapters: self::TEST_DIR);
        self::getProcessor()->run($config);
        $actual = Util::getFilesContents(self::TEST_DIR);
        self::assertSame(self::EXPECTED, $actual);
    }

    public static function getProcessor(): DirectoryProcessor
    {
        return new DirectoryProcessor(FileProcessorTest::getProcessor());
    }
}
