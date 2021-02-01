<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Process;

use LTS\MarkdownTools\Helper;
use LTS\MarkdownTools\ProcessorInterface;
use RuntimeException;

final class RunnableCodeSnippetProcess implements ProcessorInterface
{
    private const FIND_SNIPPETS_REGEX = <<<REGEXP
%^\\[Code Snippet]\\((?<file_path>[^)]+?)\\)[^`]+?```php\n(?<snippet>[^`]*?)\n```%sm
REGEXP;

    private const FIND_TOP_LEVEL_ECHO_REGEX = <<<'REGEXP'
%echo %m
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
            $codeOutput       = $this->runCodeAndGetOutput($code, $codeRealPath);
            $fullFind         = $match;
            $fullReplace      = "[Code Snippet]({$codeRelativePath})\n\n```php\n{$code}\n{$codeOutput}```";
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

    private function runCodeAndGetOutput(string $code, string $codeRealPath): string
    {
        if (preg_match(pattern: self::FIND_TOP_LEVEL_ECHO_REGEX, subject: $code) !== 1) {
            return '';
        }
        exec('unset XDEBUG_SESSION && php -f ' . $codeRealPath, $output, $exitCode);
        $output = trim(implode("\n", $output));
        if ($exitCode !== 0) {
            throw new RuntimeException("Failed running snippet:\n{$output}");
        }

        return "\n?>\n\nOUTPUT:\n\n{$output}\n\n";
    }
}
