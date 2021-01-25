<?php

declare(strict_types=1);

namespace LTS\MarkdownTools;

interface ProcessorInterface
{
    public function getProcessedContents(string $currentContents, string $currentFileDir): string;
}
