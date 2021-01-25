<?php

declare(strict_types=1);

namespace LTS\MarkdownTools;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class DirectoryProcessor
{
    public function __construct(
        private FileProcessor $fileProcessor
    ) {
    }

    /** @throws ProcessingException */
    public function run(RunConfig $config): void
    {
        $iterator = $this->getIterator($config);
        /** @var SplFileInfo $item */
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                continue;
            }
            $this->fileProcessor->processFile($item->getPathname());
        }
    }

    /** @return RecursiveIteratorIterator<RecursiveDirectoryIterator> */
    private function getIterator(RunConfig $config): RecursiveIteratorIterator
    {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($config->getPathToChapters()),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
    }
}
