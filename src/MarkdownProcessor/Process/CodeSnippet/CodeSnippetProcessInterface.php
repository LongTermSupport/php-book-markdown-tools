<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\MarkdownProcessor\Process\CodeSnippet;

interface CodeSnippetProcessInterface
{
    /**
     * A standard code snippet that is just to be copied and pasted in.
     */
    public const STANDARD_TYPE = 'Code Snippet';

    public const REPLACE_FORMAT             = "[%s](%s)\n\n```%s\n%s\n```";
    public const REPLACE_FORMAT_WITH_OUTPUT = self::REPLACE_FORMAT . "\n\n###### Output:\n```%s %s\n%s\n```";
    public const OUTPUT_LANG                = 'terminal';

    public function getProcessedReplacement(
        string $codeRelativePath,
        string $snippetType,
        string $currentFileDir,
        string $lang
    ): string;

    public function shouldProcess(string $filePath): bool;
}
