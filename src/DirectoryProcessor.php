<?php

declare(strict_types=1);

namespace LTS\MarkdownTools;

use LTS\MarkdownTools\Config\PathToChaptersConfigInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

final class DirectoryProcessor
{
    public function __construct(
        private FileProcessor $fileProcessor
    ) {
    }

    /** @throws ProcessingException */
    public function run(PathToChaptersConfigInterface $config): void
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
    private function getIterator(PathToChaptersConfigInterface $config): RecursiveIteratorIterator
    {
        if (is_dir($config->getPathToChapters()) === false) {
            throw new RuntimeException(
                $config->getPathToChapters() . ' is not a directory, config is invalid'
            );
        }

        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($config->getPathToChapters()),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
    }
}
