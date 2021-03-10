<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\MarkdownProcessor;

use LTS\MarkdownTools\Cache;
use LTS\MarkdownTools\ConsoleOutput;
use LTS\MarkdownTools\DirectoryProcessor;
use LTS\MarkdownTools\FileProcessor;
use LTS\MarkdownTools\MarkdownProcessor\Process\BlockQuote\CatchallLinkProcess;
use LTS\MarkdownTools\MarkdownProcessor\Process\BlockQuote\DocsLinkProcess;
use LTS\MarkdownTools\MarkdownProcessor\Process\BlockQuote\GithubLinkProcess;
use LTS\MarkdownTools\MarkdownProcessor\Process\BlockQuote\WikipediaLinkProcess;
use LTS\MarkdownTools\MarkdownProcessor\Process\BlockQuoteProcessor;
use LTS\MarkdownTools\MarkdownProcessor\Process\CodeSnippet\GithubCodeSnippetProcess;
use LTS\MarkdownTools\MarkdownProcessor\Process\CodeSnippet\LocalCodeSnippetProcess;
use LTS\MarkdownTools\MarkdownProcessor\Process\CodeSnippetProcessor;
use LTS\MarkdownTools\Util\Curl;

final class Factory
{
    public static function create(RunConfig $config): DirectoryProcessor
    {
        $cache      = new Cache($config->getCachePath());
        $curl       = new Curl();
        $urlFetcher = new CachingUrlFetcher($cache, $curl);
        $output     = new ConsoleOutput();

        return new DirectoryProcessor(
            new FileProcessor(
                new CodeSnippetProcessor(
                    $output,
                    new LocalCodeSnippetProcess(),
                    new GithubCodeSnippetProcess($urlFetcher)
                ),
                new BlockQuoteProcessor(
                    new DocsLinkProcess($urlFetcher),
                    new GithubLinkProcess($urlFetcher),
                    new WikipediaLinkProcess($urlFetcher),
                    new CatchallLinkProcess($urlFetcher)
                )
            )
        );
    }
}
