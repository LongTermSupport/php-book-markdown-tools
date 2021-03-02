<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\MarkdownProcessor\Process\BlockQuote\Link;

interface ShortenCallableInterface
{
    public function __invoke(string $longUrl): string;
}
