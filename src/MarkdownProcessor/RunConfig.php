<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\MarkdownProcessor;

use LTS\MarkdownTools\Config\PathToChaptersConfigInterface;

final class RunConfig implements PathToChaptersConfigInterface
{
    public const VAR_PATH = __DIR__ . '/../../var';

    public function __construct(
        private string $pathToChapters,
        private ?string $cachePath = null
    ) {
    }

    public function getCachePath(): ?string
    {
        return $this->cachePath;
    }

    public function getPathToChapters(): string
    {
        return $this->pathToChapters;
    }
}
