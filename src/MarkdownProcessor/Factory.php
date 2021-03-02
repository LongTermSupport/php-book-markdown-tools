<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\MarkdownProcessor;

use LTS\MarkdownTools\Cache;
use LTS\MarkdownTools\DirectoryProcessor;
use LTS\MarkdownTools\FileProcessor;
use LTS\MarkdownTools\MarkdownProcessor\Process\BlockQuote\DocsLinkProcess;
use LTS\MarkdownTools\MarkdownProcessor\Process\BlockQuote\GithubLinkProcess;
use LTS\MarkdownTools\MarkdownProcessor\Process\BlockQuote\WikipediaLinkProcess;
use LTS\MarkdownTools\MarkdownProcessor\Process\BlockQuoteProcessor;
use LTS\MarkdownTools\MarkdownProcessor\Process\CodeSnippet\GithubCodeSnippetProcess;
use LTS\MarkdownTools\MarkdownProcessor\Process\CodeSnippet\LocalCodeSnippetProcess;
use LTS\MarkdownTools\MarkdownProcessor\Process\CodeSnippetProcessor;
use LTS\MarkdownTools\RunConfig;

final class Factory
{
    public static function create(RunConfig $config): DirectoryProcessor
    {
        $cache      = new Cache($config->getCachePath());
        $urlFetcher = new CachingUrlFetcher($cache);

        return new DirectoryProcessor(
            new FileProcessor(
                new CodeSnippetProcessor(
                    new LocalCodeSnippetProcess(),
                    new GithubCodeSnippetProcess($urlFetcher)
                ),
                new BlockQuoteProcessor(
                    new DocsLinkProcess($urlFetcher),
                    new GithubLinkProcess($urlFetcher),
                    new WikipediaLinkProcess($urlFetcher)
                )
            )
        );
    }
}
