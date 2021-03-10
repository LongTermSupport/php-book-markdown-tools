<?php

declare(strict_types=1);

namespace Util;

use LTS\MarkdownTools\Util\Curl;
use PHPUnit\Framework\TestCase;

class CurlTest extends TestCase
{
    public function provideUrlsToRegex(): \Generator
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
}