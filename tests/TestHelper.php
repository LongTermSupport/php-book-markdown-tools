<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test;

use InvalidArgumentException;
use LTS\MarkdownTools\Cache;
use LTS\MarkdownTools\MarkdownProcessor\Factory;
use LTS\MarkdownTools\MarkdownProcessor\RunConfig;
use LTS\MarkdownTools\Util\Helper;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

final class TestHelper
{
    public const PROJECT_ROOT_PATH = __DIR__ . '/../';
    public const FIXTURE_PATH      = __DIR__ . '/Fixture/';
    public const CHAPTER_SUB_PATH  = '/Fixture/Chapters/';
    public const CHAPTER1_SUB_PATH = self::CHAPTER_SUB_PATH . '/Bar/Baz/Chapter1.md';
    public const CODE_SUB_PATH     = '/Fixture/Code/';
    public const VAR_PATH          = __DIR__ . '/../var/tests/';
    public const CACHE_PATH        = __DIR__ . '/../var/tests-cache/';
    private static Cache $cache;

    public static function nuke(): void
    {
        exec('rm -rf ' . self::VAR_PATH);
    }

    /**
     * Setup a clean copy of fixtures in var path.
     */
    public static function setupFixtures(string $varPath): void
    {
        self::createVarDir($varPath);
        $fixturesPath = Helper::resolveRelativePath(self::FIXTURE_PATH);
        $command      = "cp -r {$fixturesPath} {$varPath}/ ";
        exec($command, $output, $exitCode);
        if ($exitCode !== 0) {
            throw new RuntimeException('Failed prepping work dir: ' . implode("\n", $output));
        }
    }

    /**
     * Setup a clean copy of the fixtures then run the markdown process on them.
     *
     * @throws \LTS\MarkdownTools\ProcessingException
     */
    public static function setupProcessedFixtures(string $varPath): RunConfig
    {
        self::setupFixtures($varPath);
        $config = new RunConfig(
            pathToChapters: $varPath . self::CHAPTER_SUB_PATH,
            cachePath: self::CACHE_PATH
        );
        Factory::create($config)->run($config);

        return $config;
    }

    public static function getCache(): Cache
    {
        return self::$cache ?? (self::$cache = new Cache(self::CACHE_PATH));
    }

    public static function createVarDir(string $createDir): void
    {
        self::assertDirInVarDir($createDir);
        if (!is_dir(filename: $createDir)) {
            \Safe\mkdir(pathname: $createDir, mode: 0777, recursive: true);
        }
        exec("rm -rf {$createDir}/*");
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
        $dir       = Helper::resolveRelativePath($dir);
        $keyOffset = strlen(string: Helper::resolveRelativePath(self::VAR_PATH));
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

    private static function assertDirInVarDir(string $dir): void
    {
        if (str_starts_with(haystack: $dir, needle: '/') === false) {
            throw new InvalidArgumentException('invalid directory ' . $dir . ', must start with /');
        }
        $varPath = Helper::resolveRelativePath(relativePath: self::VAR_PATH);
        $dir     = Helper::resolveRelativePath(relativePath: $dir);
        if (str_starts_with(haystack: $dir, needle: $varPath) === false) {
            throw new InvalidArgumentException(
                'invalid directory, ' . $dir .
                ', must be sub dir of ' . $varPath
            );
        }
    }
}
