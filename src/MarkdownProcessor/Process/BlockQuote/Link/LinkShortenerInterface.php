<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\MarkdownProcessor\Process\BlockQuote\Link;

interface LinkShortenerInterface
{
    public function getShortenedLinkMarkDown(string $longUrl): string;
}
