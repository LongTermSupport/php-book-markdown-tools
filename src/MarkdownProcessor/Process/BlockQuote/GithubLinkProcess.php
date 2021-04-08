<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\MarkdownProcessor\Process\BlockQuote;

use LTS\MarkdownTools\Cache;
use LTS\MarkdownTools\MarkdownProcessor\CachingUrlFetcher;
use LTS\MarkdownTools\Util\LinkShortener\GithubLinkShortener;

final class GithubLinkProcess implements BlockQuoteProcess
{
    private const URL_REGEXP = <<<'REGEXP'
%https://github.com/.+%
REGEXP;
    private LinkProcessor       $linkProcessor;
    private GithubLinkShortener $linkShortener;

    public function __construct(
        private CachingUrlFetcher $urlFetcher,
        ?LinkProcessor $linkProcessor = null,
        ?GithubLinkShortener $linkShortener = null,
        ?Cache $cache = null
    ) {
        $this->linkProcessor = $linkProcessor ?? new LinkProcessor(self::URL_REGEXP, $this->urlFetcher);
        $this->linkShortener = $linkShortener ?? new GithubLinkShortener($cache);
    }

    public function shouldProcess(string $blockquote): bool
    {
        return $this->linkProcessor->shouldProcess($blockquote);
    }

    public function processBlockQuote(string $blockquote): string
    {
        return $this->linkProcessor->processBlockQuote($blockquote, $this->linkShortener);
    }
}
