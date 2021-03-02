<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\MarkdownProcessor\Process\CodeSnippet;

interface CodeSnippetProcessInterface
{
    /**
     * A standard code snippet that is just to be copied and pasted in.
     */
    public const STANDARD_TYPE = 'Code Snippet';

    public const REPLACE_FORMAT = "[%s](%s)\n\n```php\n%s\n%s```";

    public function getProcessedReplacement(
        string $codeRelativePath,
        string $snippetType,
        string $currentFileDir
    ): string;

    public function shouldProcess(string $filePath): bool;
}
