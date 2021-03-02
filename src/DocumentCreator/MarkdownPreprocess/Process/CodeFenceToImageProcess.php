<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\DocumentCreator\MarkdownPreprocess\Process;

use LTS\MarkdownTools\ProcessorInterface;
use LTS\MarkdownTools\RunConfig;
use RuntimeException;

final class CodeFenceToImageProcess implements ProcessorInterface
{
    public const  FIND_FENCE_BLOCKS_REGEX = <<<REGEXP
%```(?<lang>.+?)?\n(?<snippet>.*?)\n```%sm
REGEXP;
    private const JS_CONVERTER_PATH       = __DIR__ . '/../../../../js/';
    private const VAR_PATH                = RunConfig::VAR_PATH . '/codeToImage/';
    private const CODE_TMP_PATH           = self::VAR_PATH . '/codetemp.txt';
    private const CONVERT_CMD             = 'cd ' . self::JS_CONVERTER_PATH
                                            . ' &&  node convert.js --lang %s --path ' . self::CODE_TMP_PATH . ' 2>&1 ';
    private const CREATED_IMAGE_PATH      = self::JS_CONVERTER_PATH . 'highlighted-code.png';
    public const  CHAPTER_IMAGE_FOLDER    = '/code-images/';

    public function getProcessedContents(string $currentContents, string $currentFileDir): string
    {
        \Safe\preg_match_all(self::FIND_FENCE_BLOCKS_REGEX, $currentContents, $matches);
        foreach ($matches[0] as $index => $match) {
            $lang = 'none';
            if ($matches['lang'][$index] !== '') {
                $lang = $matches['lang'][$index];
            }
            $snippet = $matches['snippet'][$index];
            $this->copyCodeToTemp($snippet);
            $this->createCodeImage($lang);
            $imagePath       = $this->copyImageToDirectory($currentFileDir, md5($snippet));
            $currentContents = $this->replaceCodeFenceWithImage($match, $imagePath, $currentContents);
        }

        return $currentContents;
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
        $command = sprintf(self::CONVERT_CMD, trim($lang));
        exec($command, $output, $exitCode);
        if ($exitCode !== 0) {
            throw new RuntimeException(
                'Failed creating image with command ' . $command . "\n" . implode("\n", $output)
            );
        }
    }

    private function copyImageToDirectory(string $currentFileDir, string $snippetHash): string
    {
        $imageDir = $currentFileDir . self::CHAPTER_IMAGE_FOLDER;
        if (!is_dir($imageDir)) {
            if (!mkdir($imageDir, 0777, true) && !is_dir($imageDir)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $imageDir));
            }
        }
        $imagePath = "{$imageDir}/{$snippetHash}.png";
        \Safe\file_put_contents($imagePath, \Safe\file_get_contents(self::CREATED_IMAGE_PATH));

        return $imagePath;
    }

    private function replaceCodeFenceWithImage(string $fullFind, string $imagePath, string $currentContents): string
    {
        $fullReplace = '![](.' . self::CHAPTER_IMAGE_FOLDER . basename($imagePath) . ')';

        return \str_replace($fullFind, $fullReplace, $currentContents);
    }
}
