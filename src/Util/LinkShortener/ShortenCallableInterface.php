<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Util\LinkShortener;

interface ShortenCallableInterface
{
    public function __invoke(string $longUrl): string;
}
