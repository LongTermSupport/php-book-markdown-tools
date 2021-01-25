<?php

declare(strict_types=1);

namespace LTS\MarkdownTools;

use Throwable;

final class FileProcessor
{
    /** @var array<int,ProcessorInterface> */
    private array $processes;

    public function __construct(ProcessorInterface ...$processes)
    {
        $this->processes = $processes;
    }

    /** @throws ProcessingException */
    public function processFile(string $path): void
    {
        try {
            $contents = \Safe\file_get_contents($path);
            $contents = $this->processContents(contents: $contents, currentFileDir: basename($path));
            \Safe\file_put_contents($path, $contents);
        } catch (Throwable $throwable) {
            throw new ProcessingException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }

    private function processContents(string $contents, string $currentFileDir): string
    {
        foreach ($this->processes as $process) {
            $contents = $process->getProcessedContents($contents, $currentFileDir);
        }

        return $contents;
    }
}
