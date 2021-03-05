<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\DocumentCreator\MarkdownPreprocess\Process;

use LTS\MarkdownTools\ConsoleOutput;
use LTS\MarkdownTools\DocumentCreator\MarkdownPreprocess\RunConfig;
use LTS\MarkdownTools\ProcessorInterface;
use RuntimeException;

final class CodeFenceToImageProcess implements ProcessorInterface
{
    public const  FIND_FENCE_BLOCKS_REGEX = <<<REGEXP
%^```(?<lang>[^\n]+?)?(?<command> [^\n]+?)?\n(?<snippet>.*?)\n```%sm
REGEXP;
    public const  CHAPTER_IMAGE_FOLDER    = '/generated-images/';
    public const  LANG_PHP                = 'php';
    public const  LANG_TERMINAL           = 'terminal';
    private const JS_CONVERTER_PATH       = __DIR__ . '/../../../../js/';
    private const VAR_PATH                = RunConfig::VAR_PATH . '/codeToImage/';
    private const CODE_TMP_PATH           = self::VAR_PATH . '/codetemp.txt';
    private const REDIRECT_STDERR         = ' 2>&1 ';
    private const CONVERT_CODE_CMD        = 'cd ' . self::JS_CONVERTER_PATH
                                            . ' &&  node convert.js --lang %s --path ' . self::CODE_TMP_PATH;
    private const CONVERT_TERMINAL_CMD    = self::CONVERT_CODE_CMD . ' --command "%s"';
    private const CREATED_IMAGE_PATH      = self::JS_CONVERTER_PATH . 'generated-image.png';

    public function __construct(private RunConfig $runConfig, private ConsoleOutput $consoleOutput)
    {
    }

    public function getProcessedContents(string $currentContents, string $currentFileDir): string
    {
        \Safe\preg_match_all(self::FIND_FENCE_BLOCKS_REGEX, $currentContents, $matches);
        foreach ($matches[0] as $index => $match) {
            $lang = $matches['lang'][$index];
            if (!$this->shouldProcessLang($lang)) {
                continue;
            }
            $command     = $matches['command'][$index];
            $snippet     = $matches['snippet'][$index];
            $snippetHash = md5($snippet);
            $imagePath   = $this->getImagePath($currentFileDir, $snippetHash);
            if (false === $this->imageAlreadyExists($imagePath)) {
                $this->copyCodeToTemp($snippet);
                $lang === self::LANG_TERMINAL
                    ? $this->createTerminalImage($command)
                    : $this->createCodeImage($lang);
                $this->copyImageToDirectory($imagePath);
            }

            $currentContents = $this->replaceCodeFenceWithImage($match, $imagePath, $currentContents);
        }

        return $currentContents;
    }

    private function shouldProcessLang(string $lang): bool
    {
        if ($lang === self::LANG_TERMINAL) {
            return $this->runConfig->isConvertOutputToTerminalImage();
        }

        return $this->runConfig->isConvertCodeToImage();
    }

    private function getImagePath(string $currentFileDir, string $snippetHash): string
    {
        $imageDir = $currentFileDir . self::CHAPTER_IMAGE_FOLDER;

        return "{$imageDir}/{$snippetHash}.png";
    }

    private function imageAlreadyExists(string $imagePath): bool
    {
        return file_exists($imagePath);
    }

    private function copyCodeToTemp(string $snippet): void
    {
        if (!is_dir(self::VAR_PATH)) {
            \Safe\mkdir(self::VAR_PATH, 0777, true);
        }
        \Safe\file_put_contents(self::CODE_TMP_PATH, $snippet);
    }

    private function createCodeImage(string $lang): void
    {
        $command = sprintf(self::CONVERT_CODE_CMD, trim($lang));
        $this->runCommand($command);
    }

    private function createTerminalImage(string $terminalCommand): void
    {
        $command = sprintf(self::CONVERT_TERMINAL_CMD, self::LANG_TERMINAL, trim($terminalCommand));
        $this->runCommand($command);
    }

    private function runCommand(string $command): void
    {
        $command .= self::REDIRECT_STDERR;
        $this->consoleOutput->stdOut('runCommand: ' . $command);
        exec($command, $output, $exitCode);
        if ($exitCode !== 0) {
            throw new RuntimeException(
                'Failed creating image with command ' . $command . "\n" . implode("\n", $output)
            );
        }
    }

    private function copyImageToDirectory(string $imagePath): void
    {
        $imageDir = dirname($imagePath);
        if (!is_dir($imageDir)) {
            if (!mkdir($imageDir, 0777, true) && !is_dir($imageDir)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $imageDir));
            }
        }
        \Safe\file_put_contents($imagePath, \Safe\file_get_contents(self::CREATED_IMAGE_PATH));
    }

    private function replaceCodeFenceWithImage(string $fullFind, string $imagePath, string $currentContents): string
    {
        $fullReplace = '![](.' . self::CHAPTER_IMAGE_FOLDER . basename($imagePath) . ')';

        return \str_replace($fullFind, $fullReplace, $currentContents);
    }
}
