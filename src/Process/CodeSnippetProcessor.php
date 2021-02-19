<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Process;

use LTS\MarkdownTools\Process\CodeSnippet\CodeSnippetProcessInterface;
use LTS\MarkdownTools\ProcessorInterface;
use RuntimeException;

final class CodeSnippetProcessor implements ProcessorInterface
{
    private const FIND_SNIPPETS_REGEX = <<<REGEXP
%^\\[(?<snippet_type>Code[^\\[]+Snippet)]\\((?<file_path>[^)]+?)\\)[^`]+?```php\n(?<snippet>[^`]*?)\n```%sm
REGEXP;
    /** @var CodeSnippetProcessInterface[] */
    private array $processInterfaces;

    public function __construct(CodeSnippetProcessInterface ...$processInterfaces)
    {
        $this->processInterfaces = $processInterfaces;
    }

    public function getProcessedContents(string $currentContents, string $currentFileDir): string
    {
        preg_match_all(self::FIND_SNIPPETS_REGEX, $currentContents, $matches);
        foreach ($matches[0] as $index => $match) {
            $fullFind        = $match;
            $fullReplace     = $this->getReplace($matches, $index, $currentFileDir);
            $currentContents = str_replace($fullFind, $fullReplace, $currentContents);
        }

        return $currentContents;
    }

    /**
     * @param array<mixed,string> $matches
     */
    private function getReplace(array $matches, int $index, string $currentFileDir): string
    {
        $filePath    = $matches['file_path'][$index];
        $snippetType = $matches['snippet_type'][$index];
        foreach ($this->processInterfaces as $process) {
            if ($process->shouldProcess($filePath)) {
                return $process->getProcessedReplacement($filePath, $snippetType, $currentFileDir);
            }
        }
        throw new RuntimeException('Failed finding processor for snippet: ' . $matches[0][$index]);
    }
}
