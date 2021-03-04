<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Util\LinkShortener;

interface LinkShortenerInterface
{
    public function getShortenedLinkMarkDown(string $longUrl): string;

    public function canShorten(string $longUrl): bool;
}
