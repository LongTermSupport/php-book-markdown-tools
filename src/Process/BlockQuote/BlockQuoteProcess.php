<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Process\BlockQuote;

interface BlockQuoteProcess
{
    public function shouldProcess(string $blockquote): bool;

    public function processBlockQuote(string $blockquote): string;
}
