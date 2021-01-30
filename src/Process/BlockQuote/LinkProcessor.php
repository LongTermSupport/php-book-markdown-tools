<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Process\BlockQuote;

use LTS\MarkdownTools\CachingUrlFetcher;
use LTS\MarkdownTools\Process\BlockQuote\Link\LinkShortenerInterface;
use RuntimeException;

final class LinkProcessor
{
    public function __construct(private string $urlRegexp, private CachingUrlFetcher $urlFetcher)
    {
    }

    public function shouldProcess(string $blockquote): bool
    {
        return preg_match(pattern: $this->urlRegexp, subject: $blockquote) === 1;
    }

    public function processBlockQuote(string $blockquote, LinkShortenerInterface $linkShortener = null): string
    {
        $result = preg_match(pattern: $this->urlRegexp, subject: $blockquote, matches: $matches);
        if ($result !== 1) {
            throw new RuntimeException('Failed finding URL in blockquote ' . $blockquote);
        }
        $url = $matches[0];

        return $this->buildBlock($url, $linkShortener);
    }

    private function getTitle(string $content): string
    {
        preg_match('%<title>([^<]+?)</%', $content, $matches);

        return $matches[1];
    }

    private function buildBlock(string $url, LinkShortenerInterface $linkShortener = null): string
    {
        $content     = $this->urlFetcher->getContents($url);
        $title       = $this->getTitle($content);
        $urlMarkdown = ($linkShortener instanceof LinkShortenerInterface)
            ? $linkShortener->getShortenedLinkMarkDown($url)
            : $url;

        return <<<MARKDOWN
> #### {$title}
> {$urlMarkdown}
MARKDOWN;
    }
}
