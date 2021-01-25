<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test;

use LTS\MarkdownTools\Factory;
use LTS\MarkdownTools\RunConfig;
use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
{
    private const TEST_CHAPTERS_DIR      = __DIR__ . '/Fixture/Foo/Bar/Baz/';
    private const TEST_CHAPTER1_PATH     = self::TEST_CHAPTERS_DIR . '/Chapter1.md';
    private const EXPECTED_CHAPTER1_PATH = __DIR__ . '/Fixture/Chapter1-Expected.md';
    private const SOURCE_CHAPTER1_PATH   = __DIR__ . '/Fixture/Chapter1-Source.md';

    /** @test */
    public function processFull(): void
    {
        \Safe\file_put_contents(
            self::TEST_CHAPTER1_PATH,
            \Safe\file_get_contents(self::SOURCE_CHAPTER1_PATH)
        );
        Factory::create()->run(new RunConfig(self::TEST_CHAPTERS_DIR));
        self::assertFileEquals(self::EXPECTED_CHAPTER1_PATH, self::TEST_CHAPTER1_PATH);
    }
}