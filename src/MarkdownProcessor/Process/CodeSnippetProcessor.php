<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\MarkdownProcessor\Process;

use LTS\MarkdownTools\ConsoleOutputInterface;
use LTS\MarkdownTools\MarkdownProcessor\Process\CodeSnippet\CodeSnippetProcessInterface;
use LTS\MarkdownTools\ProcessorInterface;
use RuntimeException;

final class CodeSnippetProcessor implements ProcessorInterface
{
    public const FIND_SNIPPETS_REGEX = <<<REGEXP
%^\\[(?<snippet_type>Code[^\\[]+Snippet)]\\((?<file_path>[^)]+?)\\)[^`]+?```(?<lang>.+?)\n(?<snippet>.*?)\n```(\n\n###### Output:\n```(?<output_lang>.+?)?(?<command> .+?)?\n(?<output>.*?\n```))?%sm
REGEXP;
    public const WARN_LENGTH_LINES   = 45;

    /** @var CodeSnippetProcessInterface[] */
    private array $processInterfaces;

    public function __construct(
        private ConsoleOutputInterface $output,
        CodeSnippetProcessInterface ...$processInterfaces
    ) {
        $this->processInterfaces = $processInterfaces;
    }

    public function getProcessedContents(string $currentContents, string $currentFileDir): string
    {
        preg_match_all(self::FIND_SNIPPETS_REGEX, $currentContents, $matches);
        foreach ($matches[0] as $index => $match) {
            $filePath    = $matches['file_path'][$index];
            $snippetType = $matches['snippet_type'][$index];
            $lang        = $matches['lang'][$index];
            $fullFind    = $match;
            $fullReplace = $this->getReplace($filePath, $snippetType, $currentFileDir, $lang);
            $this->errIfLongerThan($filePath, $snippetType, $fullReplace);
            $currentContents = str_replace($fullFind, $fullReplace, $currentContents);
        }

        return $currentContents;
    }

    private function errIfLongerThan(string $filePath, string $snippetType, string $fullReplace): void
    {
        $lines = substr_count(haystack: $fullReplace, needle: "\n");
        if ($lines > self::WARN_LENGTH_LINES) {
            $this->output->stdErr("Warning ({$snippetType}) {$filePath} is {$lines} lines ");
        }
    }

    private function getReplace(string $filePath, string $snippetType, string $currentFileDir, string $lang): string
    {
        foreach ($this->processInterfaces as $process) {
            if ($process->shouldProcess($filePath)) {
                return $process->getProcessedReplacement($filePath, $snippetType, $currentFileDir, $lang);
            }
        }
        throw new RuntimeException('Failed finding processor for snippet: ' . $filePath);
    }
}
