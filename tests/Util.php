<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test;

use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

final class Util
{
    public const VAR_PATH = __DIR__ . '/../var/tests/';

    public static function nuke(): void
    {
        exec('rm -rf ' . self::VAR_PATH);
    }

    public static function createVarDir(string $createDir): void
    {
        self::assertDirInVarDir($createDir);
        if (!is_dir(filename: $createDir)) {
            \Safe\mkdir(pathname: $createDir, mode: 0777, recursive: true);
        }
    }

    public static function createTestFile(
        string $contents = '',
        string $filename = null,
        string $createInDir = self::VAR_PATH
    ): string {
        if ($filename === null) {
            $filename = debug_backtrace(options: 0, limit: 2)[1]['function'] . '.txt';
        }
        self::assertDirInVarDir($createInDir);
        self::createVarDir($createInDir);
        $path = "{$createInDir}/{$filename}";
        \Safe\file_put_contents(filename: $path, data: $contents);

        return $path;
    }

    /**
     * @throws \Safe\Exceptions\FilesystemException
     *
     * @return array<string,string>
     */
    public static function getFilesContents(string $dir): array
    {
        $dir       = self::resolveRelativePath($dir);
        $keyOffset = strlen(string: self::resolveRelativePath(self::VAR_PATH));
        $return    = [];
        $iterator  = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($iterator as $path => $fileInfo) {
            if ($fileInfo->isDir()) {
                continue;
            }
            $key          = substr(string: $path, offset: $keyOffset);
            $return[$key] = \Safe\file_get_contents($path);
        }

        return $return;
    }

    private static function resolveRelativePath(string $relativePath): string
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

    private static function assertDirInVarDir(string $dir): void
    {
        if (str_starts_with(haystack: $dir, needle: '/') === false) {
            throw new InvalidArgumentException('invalid directory ' . $dir . ', must start with /');
        }
        $varPath = self::resolveRelativePath(relativePath: self::VAR_PATH);
        $dir     = self::resolveRelativePath(relativePath: $dir);
        if (str_starts_with(haystack: $dir, needle: $varPath) === false) {
            throw new InvalidArgumentException(
                'invalid directory, ' . $dir .
                ', must be sub dir of ' . $varPath
            );
        }
    }
}
