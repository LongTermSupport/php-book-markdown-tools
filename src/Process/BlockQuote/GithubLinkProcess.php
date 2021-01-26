<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Process\BlockQuote;

use LTS\MarkdownTools\CachingUrlFetcher;
use RuntimeException;

final class GithubLinkProcess implements BlockQuoteProcess
{
    private const URL_REGEXP = <<<'REGEXP'
%https://github.com/.+%
REGEXP;

    public function __construct(private CachingUrlFetcher $urlFetcher)
    {
    }

    public function shouldProcess(string $blockquote): bool
    {
        return preg_match(pattern: self::URL_REGEXP, subject: $blockquote) === 1;
    }

    public function processBlockQuote(string $blockquote): string
    {
        $result = preg_match(pattern: self::URL_REGEXP, subject: $blockquote, matches: $matches);
        if ($result !== 1) {
            throw new RuntimeException('Failed finding URL in blockquote ' . $blockquote);
        }
        $url = $matches[0];

        return $this->buildBlock($url);
    }

    private function getTitle(string $content): string
    {
        preg_match('%<title>([^<]+?)</%', $content, $matches);

        return $matches[1];
    }

    private function buildBlock(string $url): string
    {
        $content = $this->urlFetcher->getContents($url);
        $title   = $this->getTitle($content);

        return <<<MARKDOWN
> #### {$title}
> {$url}
MARKDOWN;
    }
}
