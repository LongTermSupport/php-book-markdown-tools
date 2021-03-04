<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\MarkdownProcessor\Process\CodeSnippet;

use LTS\MarkdownTools\Util\Helper;
use RuntimeException;

final class LocalCodeSnippetProcess implements CodeSnippetProcessInterface
{
    private const DISABLE_XDEBUG = ' unset XDEBUG_SESSION ';
    private const REDIRECT_ERR   = ' 2>&1 ';

    /**
     * A code snippet that should be copied and pasted and we should also run, capture output and past in.
     */
    private const EXECUTABLE_TYPE = 'Code Executable Snippet';
    /**
     * A code snippet that should be copied and pasted and we should also run, it is expected to fail and we should
     * capture the error output.
     */
    private const ERROR_TYPE = 'Code Error Snippet';

    public function getProcessedReplacement(
        string $codeRelativePath,
        string $snippetType,
        string $currentFileDir,
        string $lang
    ): string {
        $codeRealPath = $this->getCodeRealPath(
            currentFileDir: $currentFileDir,
            codeRelativePath: $codeRelativePath
        );
        $code         = \Safe\file_get_contents($codeRealPath);
        if ($snippetType === self::STANDARD_TYPE) {
            return sprintf(self::REPLACE_FORMAT, $snippetType, $codeRelativePath, $lang, $code);
        }
        $codeOutput = match ($snippetType) {
            self::EXECUTABLE_TYPE => $this->getOutput($codeRealPath),
            self::ERROR_TYPE      => $this->getErrorOutput($codeRealPath),
            default               => throw new RuntimeException('Got invalid snippet type: ' . $snippetType)
        };
        $filename   = basename($codeRelativePath);
        $command    = "{$lang} {$filename}";

        return sprintf(
            self::REPLACE_FORMAT_WITH_OUTPUT,
            $snippetType,
            $codeRelativePath,
            $lang,
            $code,
            self::OUTPUT_LANG,
            $command,
            $codeOutput
        );
    }

    public function shouldProcess(string $filePath): bool
    {
        return str_starts_with(haystack: $filePath, needle: '.');
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
            throw new RuntimeException("Unexpected error running snippet:\n{$codeRealPath}\n\n{$output}");
        }
        if ($output === '') {
            throw new RuntimeException("No output running snippet:\n{$codeRealPath}");
        }

        return $this->formatOutput($output);
    }

    private function getErrorOutput(string $codeRealPath): string
    {
        [$exitCode, $output] = $this->runCode($codeRealPath);
        if ($exitCode === 0) {
            throw new RuntimeException("No expected error running snippet:\n{$codeRealPath}\n\n{$output}");
        }
        if ($output === '') {
            throw new RuntimeException("No expected error output running snippet:\n{$codeRealPath}");
        }

        return $this->formatOutput($output);
    }

    /**
     * @return mixed[] an array of exitCode and output
     */
    private function runCode(string $codeRealPath): array
    {
        exec(
            command: self::DISABLE_XDEBUG . ' && php -f ' . $codeRealPath . self::REDIRECT_ERR,
            output: $output,
            result_code: $exitCode
        );
        $output = trim(implode("\n", $output));

        return [$exitCode, $output];
    }

    private function formatOutput(string $output): string
    {
        $wrapped = wordwrap($output, width: 60);

        return "\n{$wrapped}\n";
    }
}
