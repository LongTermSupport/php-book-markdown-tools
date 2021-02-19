<?php

declare(strict_types=1);

namespace LTS\MarkdownTools;

use LTS\MarkdownTools\Process\BlockQuote\DocsLinkProcess;
use LTS\MarkdownTools\Process\BlockQuote\GithubLinkProcess;
use LTS\MarkdownTools\Process\BlockQuote\WikipediaLinkProcess;
use LTS\MarkdownTools\Process\BlockQuoteProcessor;
use LTS\MarkdownTools\Process\CodeSnippet\GithubCodeSnippetProcess;
use LTS\MarkdownTools\Process\CodeSnippet\LocalCodeSnippetProcess;
use LTS\MarkdownTools\Process\CodeSnippetProcessor;

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
