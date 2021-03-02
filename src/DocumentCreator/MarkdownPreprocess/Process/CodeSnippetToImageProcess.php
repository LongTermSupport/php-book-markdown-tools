<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\DocumentCreator\MarkdownPreprocess\Process;

use LTS\MarkdownTools\MarkdownProcessor\Process\CodeSnippet\CodeSnippetProcessInterface;
use LTS\MarkdownTools\RunConfig;

final class CodeSnippetToImageProcess implements CodeSnippetProcessInterface
{
    private const JS_CONVERTER_PATH = __DIR__ . '/../../../../js/';
    private const VAR_PATH          = RunConfig::VAR_PATH . '/codeToImage/';
    private const CODE_TMP_PATH     = self::VAR_PATH . '/codetemp.php';
    private const CONVERT_CMD       = 'cd ' . self::JS_CONVERTER_PATH
                                      . ' &&  node convert.js --path ' . self::CODE_TMP_PATH;

    public function getProcessedReplacement(
        string $codeRelativePath,
        string $snippetType,
        string $currentFileDir
    ): string {
        return '';
    }

    public function shouldProcess(string $filePath): bool
    {
        return true;
    }

    private function copyCodeToTemp(string $codeRelativePath, string $currentFileDir): void
    {
    }

    private function createCodeImage(): string
    {
        exec(self::CONVERT_CMD, $output, $exitCode);

        return '';
    }
}
