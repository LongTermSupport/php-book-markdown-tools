<?php

declare(strict_types=1);

namespace LTS\MarkdownTools;

use LTS\MarkdownTools\Process\BlockQuote\DocsLinkProcess;
use LTS\MarkdownTools\Process\BlockQuote\GithubLinkProcess;
use LTS\MarkdownTools\Process\BlockQuote\WikipediaLinkProcess;
use LTS\MarkdownTools\Process\BlockQuoteProcessor;
use LTS\MarkdownTools\Process\RunnableCodeSnippetProcess;

final class Factory
{
    public static function create(RunConfig $config): DirectoryProcessor
    {
        $urlFetcher = new CachingUrlFetcher($config->getCachePath());

        return new DirectoryProcessor(
            new FileProcessor(
                new RunnableCodeSnippetProcess(),
                new BlockQuoteProcessor(
                    new DocsLinkProcess($urlFetcher),
                    new GithubLinkProcess($urlFetcher),
                    new WikipediaLinkProcess($urlFetcher)
                )
            )
        );
    }
}