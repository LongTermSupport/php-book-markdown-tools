<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Process\BlockQuote;

use LTS\MarkdownTools\Cache;
use LTS\MarkdownTools\CachingUrlFetcher;
use LTS\MarkdownTools\Process\BlockQuote\Link\WikipediaLinkShortener;

final class WikipediaLinkProcess implements BlockQuoteProcess
{
    private const URL_REGEXP = <<<'REGEXP'
%(?<=\()?https://en.wikipedia.org/.+(?=\))?%
REGEXP;

    /**
     * For example, this link:
     * https://en.wikipedia.org/w/index.php?title=Data_type&oldid=996603474#:~:text=In%20computer%20science%20and%20computer,intends%20to%20use%20the%20data.
     *
     * Note that we are forcing linking to a specific page version to ensure the highlight works correctly
     *
     * To get the correct versioned page link, visit the current page, and then click the "View history" tab and select
     * the version you want to link to.
     */
    private const HIGHLIGHTED_TEXT_REGEXP = <<<'REGEXP'
%(?<=\()?https://en\.wikipedia\.org/w/index\.php\?title=(?<Title>[^&]+?)&oldid=(?<OldId>\d+?)#:~:text=(?<HighlightedText>.+)(?=\))?%
REGEXP;

    private LinkProcessor          $linkProcessor;
    private WikipediaLinkShortener $shortener;

    public function __construct(
        private CachingUrlFetcher $urlFetcher,
        ?LinkProcessor $linkProcessor = null,
        ?WikipediaLinkShortener $linkShortener = null,
        ?Cache $cache = null
    ) {
        $this->linkProcessor = $linkProcessor ?? new LinkProcessor(self::URL_REGEXP, $this->urlFetcher);
        $this->shortener     = $linkShortener ?? new WikipediaLinkShortener($cache);
    }

    public function shouldProcess(string $blockquote): bool
    {
        return $this->linkProcessor->shouldProcess($blockquote);
    }

    public function processBlockQuote(string $blockquote): string
    {
        $blockquote = $this->linkProcessor->processBlockQuote($blockquote, $this->shortener);

        return $this->addHighlightedText($blockquote);
    }

    private function addHighlightedText(string $blockquote): string
    {
        if (\Safe\preg_match(self::HIGHLIGHTED_TEXT_REGEXP, $blockquote, $matches) !== 1) {
            return $blockquote;
        }
        $text    = urldecode($matches['HighlightedText']);
        $wrapped = wordwrap($text, width: 40, break: "\n> ", cut_long_words: false);

        return "{$blockquote}\n> {$wrapped}";
    }
}
