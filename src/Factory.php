<?php

declare(strict_types=1);

namespace LTS\MarkdownTools;

use LTS\MarkdownTools\Process\BlockQuote\DocsLinkProcess;
use LTS\MarkdownTools\Process\BlockQuoteProcessor;
use LTS\MarkdownTools\Process\RunnableCodeSnippetProcess;

final class Factory
{
    public static function create(): DirectoryProcessor
    {
        return new DirectoryProcessor(
            new FileProcessor(
                new RunnableCodeSnippetProcess(),
                new BlockQuoteProcessor(
                    new DocsLinkProcess()
                )
            )
        );
    }
}