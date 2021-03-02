<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test\MarkdownProcessor;

use LTS\MarkdownTools\MarkdownProcessor\Factory;
use LTS\MarkdownTools\RunConfig;
use LTS\MarkdownTools\Test\TestHelper;
use PHPUnit\Framework\TestCase;

/**
 * This test simulates teh full system being run.
 * We run it multiple times to ensure that the system is idempotent.
 *
 * @internal
 * @coversNothing
 * @small
 */
final class IntegrationTest extends TestCase
{
    private const TEST_CHAPTERS_DIR      = TestHelper::FIXTURE_PATH . '/Foo/Bar/Baz/';
    private const TEST_CHAPTER1_PATH     = self::TEST_CHAPTERS_DIR . '/Chapter1.md';
    private const EXPECTED_CHAPTER1_PATH = TestHelper::FIXTURE_PATH . '/Chapter1-Expected.md';
    private const SOURCE_CHAPTER1_PATH   = TestHelper::FIXTURE_PATH . '/Chapter1-Source.md';
    private const TIMES_TO_RUN           = 4;

    /** @test */
    public function processFull(): void
    {
        \Safe\file_put_contents(
            self::TEST_CHAPTER1_PATH,
            \Safe\file_get_contents(self::SOURCE_CHAPTER1_PATH)
        );
        $config    = new RunConfig(self::TEST_CHAPTERS_DIR, TestHelper::CACHE_PATH);
        $processor = Factory::create($config);
        for ($i = 0; $i < self::TIMES_TO_RUN; ++$i) {
            $processor->run($config);
            self::assertFileEquals(
                expected: self::EXPECTED_CHAPTER1_PATH,
                actual: self::TEST_CHAPTER1_PATH,
                message: 'Tests failed on run ' . $i . ' of ' . self::TIMES_TO_RUN
            );
        }
    }
}
