<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Process;

use LTS\MarkdownTools\Helper;
use LTS\MarkdownTools\ProcessorInterface;
use RuntimeException;

final class RunnableCodeSnippetProcess implements ProcessorInterface
{
    private const DISABLE_XDEBUG = ' unset XDEBUG_SESSION ';
    private const REDIRECT_ERR   = ' 2>&1 ';
    /**
     * A standard code snippet that is just to be copied and pasted in
     */
    private const STANDARD_TYPE = 'Code Snippet';
    /**
     * A code snippet that should be copied and pasted and we should also run, capture output and past in
     */
    private const EXECUTABLE_TYPE = 'Code Executable Snippet';
    /**
     * A code snippet that should be copied and pasted and we should also run, it is expected to fail and we should
     * capture the error output
     */
    private const ERROR_TYPE = 'Code Error Snippet';

    private const FIND_SNIPPETS_REGEX = <<<REGEXP
%^\\[(?<snippet_type>Code.+?Snippet)]\\((?<file_path>[^)]+?)\\)[^`]+?```php\n(?<snippet>[^`]*?)\n```%sm
REGEXP;

    public function getProcessedContents(string $currentContents, string $currentFileDir): string
    {
        preg_match_all(self::FIND_SNIPPETS_REGEX, $currentContents, $matches);
        foreach ($matches[0] as $index => $match) {
            $codeRelativePath = $matches['file_path'][$index];
            $codeRealPath     = $this->getCodeRealPath(
                currentFileDir: $currentFileDir,
                codeRelativePath: $codeRelativePath
            );
            $code             = \Safe\file_get_contents($codeRealPath);
            $snippetType      = $matches['snippet_type'][$index];
            $codeOutput       = match ($snippetType) {
                self::STANDARD_TYPE => '',
                self::EXECUTABLE_TYPE => $this->getOutput($codeRealPath),
                self::ERROR_TYPE => $this->getErrorOutput($codeRealPath)
            };
            $fullFind         = $match;
            $fullReplace      = "[$snippetType]({$codeRelativePath})\n\n```php\n{$code}\n{$codeOutput}```";
            $currentContents  = str_replace($fullFind, $fullReplace, $currentContents);
        }

        return $currentContents;
    }


    private function getCodeRealPath(string $currentFileDir, string $codeRelativePath): string
    {
        $resolvedPath = Helper::resolveRelativePath($currentFileDir . '/' . $codeRelativePath);
        $realpath     = realpath($resolvedPath);
        if ($realpath === false) {
            throw new RuntimeException('Failed finding realpath for ' . $resolvedPath);
        }

        return $realpath;
    }

    private function getOutput(string $codeRealPath): string
    {
        [$exitCode, $output] = $this->runCode($codeRealPath);
        if ($exitCode !== 0) {
            throw new RuntimeException("Unexpected error running snippet:\n{$output}");
        }
        if ('' === $output) {
            throw new RuntimeException("No output running snippet:\n{$output}");
        }

        return $this->formatOutput($output);
    }

    private function getErrorOutput(string $codeRealPath): string
    {
        [$exitCode, $output] = $this->runCode($codeRealPath);
        if ($exitCode === 0) {
            throw new RuntimeException("No expected error running snippet:\n{$output}");
        }
        if ('' === $output) {
            throw new RuntimeException("No expected error output running snippet:\n{$output}");
        }

        return $this->formatOutput($output);
    }

    /**
     * @param string $codeRealPath
     *
     * @return int|string[] an array of exitCode and output
     */
    private function runCode(string $codeRealPath): array
    {
        exec(command: self::DISABLE_XDEBUG . ' && php -f ' . $codeRealPath . self::REDIRECT_ERR,
            output: $output,
            result_code: $exitCode);
        $output = trim(implode("\n", $output));

        return [$exitCode, $output];
    }

    private function formatOutput(string $output): string
    {
        return "\n?>\n\nOUTPUT:\n\n{$output}\n\n";
    }
}
