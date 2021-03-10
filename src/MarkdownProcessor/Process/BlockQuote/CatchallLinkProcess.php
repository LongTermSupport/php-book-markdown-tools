<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\MarkdownProcessor\Process\BlockQuote;

use LTS\MarkdownTools\MarkdownProcessor\CachingUrlFetcher;

final class CatchallLinkProcess implements BlockQuoteProcess
{
    private const URL_REGEXP = <<<'REGEXP'
%https://.+%
REGEXP;
    private LinkProcessor $linkProcessor;

    public function __construct(private CachingUrlFetcher $urlFetcher, ?LinkProcessor $linkProcessor = null)
    {
        $this->linkProcessor = $linkProcessor ?? new LinkProcessor(self::URL_REGEXP, $this->urlFetcher);
    }

    public function shouldProcess(string $blockquote): bool
    {
        return $this->linkProcessor->shouldProcess($blockquote);
    }

    public function processBlockQuote(string $blockquote): string
    {
        return $this->linkProcessor->processBlockQuote($blockquote);
    }
}
