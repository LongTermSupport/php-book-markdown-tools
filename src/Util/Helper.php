<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Util;

use RuntimeException;

final class Helper
{
    public static function resolveRelativePath(string $relativePath): string
    {
        if (str_starts_with(haystack: $relativePath, needle: '/') === false) {
            throw new RuntimeException('Relative path must start at root /, you passed ' . $relativePath);
        }
        $path = [];
        foreach (explode('/', $relativePath) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }

            if ($part !== '..') {
                $path[] = $part;
                continue;
            }
            if (count($path) > 0) {
                array_pop($path);
                continue;
            }
            throw new RuntimeException('Relative path goes too high');
        }

        return '/' . implode('/', $path);
    }

    public static function removeDuplicateUrlSlashes(string $url): string
    {
        return preg_replace('%(?<!:)/{2,}%', '/', $url)
               ?? throw new RuntimeException('Regex failure in ' . __METHOD__);
    }
}
