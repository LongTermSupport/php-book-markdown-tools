<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test\Util;

use Generator;
use LTS\MarkdownTools\Util\Curl;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 * @covers \LTS\MarkdownTools\Util\Curl
 *
 * @medium
 */
final class CurlTest extends TestCase
{
    /** @return Generator<string,array<int,string>> */
    public function provideUrlsToRegex(): Generator
    {
        yield 'git.drupalcode.org/project/drupal' => [
            'https://git.drupalcode.org/project/drupal',
            '%<title>project / drupal · GitLab</title>%',
        ];
    }

    /**
     * @test
     * @dataProvider provideUrlsToRegex
     */
    public function itCanGetUrls(string $url, string $matchRegex): void
    {
        $actual = (new Curl())->fetchUrl($url);
        self::assertMatchesRegularExpression($matchRegex, $actual);
    }

    /** @test */
    public function itHandles404(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed getting URL: https://httpstat.us/404');
        (new Curl())->fetchUrl('https://httpstat.us/404');
    }
}
