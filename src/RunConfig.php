<?php

declare(strict_types=1);

namespace LTS\MarkdownTools;

final class RunConfig
{
    public function __construct(
        private string $pathToChapters
    ) {
    }

    public function getPathToChapters(): string
    {
        return $this->pathToChapters;
    }
}
