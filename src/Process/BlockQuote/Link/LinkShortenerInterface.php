<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Process\BlockQuote\Link;

interface LinkShortenerInterface
{
    public function getShortenedLinkMarkDown(string $longUrl): string;
}
