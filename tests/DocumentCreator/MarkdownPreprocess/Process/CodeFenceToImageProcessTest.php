<?php

declare(strict_types=1);

namespace DocumentCreator\MarkdownPreprocess\Process;

use LTS\MarkdownTools\DocumentCreator\MarkdownPreprocess\Process\CodeFenceToImageProcess;
use LTS\MarkdownTools\Test\TestHelper;
use PHPUnit\Framework\TestCase;

class CodeFenceToImageProcessTest extends TestCase
{
    private CodeFenceToImageProcess $process;
    private const TEST_FILE       = 'Chapter1-Expected.md';
    private const FIXTURE_PATH    = TestHelper::FIXTURE_PATH . self::TEST_FILE;
    private const TEST_DIR        = TestHelper::VAR_PATH . 'CodeFenceToImageProcessTest/';
    private const TEST_PATH       = self::TEST_DIR . self::TEST_FILE;
    private const TEST_IMAGE_PATH = self::TEST_DIR . CodeFenceToImageProcess::CHAPTER_IMAGE_FOLDER;

    public function setUp(): void
    {
        TestHelper::nuke();
        TestHelper::createVarDir(self::TEST_DIR);
        $this->process = new CodeFenceToImageProcess();
        \Safe\file_put_contents(
            self::TEST_PATH,
            \Safe\file_get_contents(self::FIXTURE_PATH)
        );
    }

    /** @test */
    public function itConvertsFencesToImages(): void
    {
        $expected = '';
        $actual   = $this->process->getProcessedContents(
            \Safe\file_get_contents(self::TEST_PATH),
            self::TEST_DIR
        );
        self::assertDirectoryExists(self::TEST_IMAGE_PATH);
        self::assertSame($expected, $actual);
    }
}