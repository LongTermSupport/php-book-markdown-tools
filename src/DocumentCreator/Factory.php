<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\DocumentCreator;

use LTS\MarkdownTools\Cache;
use LTS\MarkdownTools\ConsoleOutput;
use LTS\MarkdownTools\DirectoryProcessor;
use LTS\MarkdownTools\DocumentCreator\MarkdownPreprocess\Process\CodeFenceToImageProcess;
use LTS\MarkdownTools\DocumentCreator\MarkdownPreprocess\Process\ConvertCodeSnippetLinkProcess;
use LTS\MarkdownTools\DocumentCreator\MarkdownPreprocess\RunConfig;
use LTS\MarkdownTools\FileProcessor;
use LTS\MarkdownTools\Util\LinkShortener\GithubLinkShortener;

final class Factory
{
    public static function create(RunConfig $runConfig): DirectoryProcessor
    {
        $consoleOutput = new ConsoleOutput();
        $cache         = new Cache();

        return new DirectoryProcessor(
            new FileProcessor(
                new CodeFenceToImageProcess($runConfig, $consoleOutput),
                new ConvertCodeSnippetLinkProcess($runConfig, new GithubLinkShortener($cache))
            )
        );
    }
}
