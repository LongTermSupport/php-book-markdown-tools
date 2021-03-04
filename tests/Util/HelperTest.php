<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test\Util;

use Generator;
use LTS\MarkdownTools\Util\Helper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 *
 * @small
 */
final class HelperTest extends TestCase
{
    /** @return Generator<string, string[]> */
    public function provideRelativePaths(): Generator
    {
        yield '/a/b/c' => ['/a/b/../b/c', '/a/b/c'];
    }

    /**
     * @test
     * @dataProvider provideRelativePaths
     */
    public function itCanResolveRelativePaths(
        string $relativePath,
        string $expected
    ): void {
        $actual = Helper::resolveRelativePath($relativePath);
        self::assertSame($expected, $actual);
    }

    /** @return Generator<string, string[]> */
    public function provideDuplicateSlashesUrls(): Generator
    {
        yield 'foo.com/a/b/c' => ['https://foo.com//a////b/c', 'https://foo.com/a/b/c'];
    }

    /**
     * @test
     * @dataProvider provideDuplicateSlashesUrls
     */
    public function itCanRemoveDuplicateUrlSlashes(
        string $extraSlashes,
        string $expected
    ): void {
        $actual = Helper::removeDuplicateUrlSlashes($extraSlashes);
        self::assertSame($expected, $actual);
    }
}
