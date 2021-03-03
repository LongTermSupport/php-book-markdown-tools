<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\DocumentCreator;

use LTS\MarkdownTools\ConsoleOutput;
use LTS\MarkdownTools\DocumentCreator\MarkdownPreprocess\Process\CodeFenceToImageProcess;
use LTS\MarkdownTools\FileProcessor;

final class Factory
{
    public static function create(): FileProcessor
    {
        $consoleOutput = new ConsoleOutput();

        return new FileProcessor(
            new CodeFenceToImageProcess($consoleOutput)
        );
    }
}
