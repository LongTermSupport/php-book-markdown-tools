<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Process\BlockQuote;

use LTS\MarkdownTools\CachingUrlFetcher;
use LTS\MarkdownTools\Process\BlockQuote\Link\LinkShortenerInterface;
use RuntimeException;

final class LinkProcessor
{
    /**
     * @var string Match but don't include a leading ( if set
     *             Includes the opening regex delim %
     */
    public const REGEX_LINK_LOOKBEHIND = '%(?<=\()';
    /**
     * @var string Match but don't include a trailing ) if set
     *             Includes teh closing regex delim %
     */
    public const REGEX_LINK_LOOKAHEAD = '(?=\))%';

    public function __construct(private string $urlRegexp, private CachingUrlFetcher $urlFetcher)
    {
    }

    public function shouldProcess(string $blockquote): bool
    {
        return preg_match(pattern: $this->urlRegexp, subject: $blockquote) === 1;
    }

    public function processBlockQuote(string $blockquote, LinkShortenerInterface $linkShortener = null): string
    {
        $url = $this->getMatchedParensWrappedUrl($blockquote) ?? $this->getMatchedUrlOrFail($blockquote);

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
> ###### {$title}
> {$urlMarkdown}
MARKDOWN;
    }

    private function getMatchedUrlOrFail(string $blockquote): string
    {
        $result = preg_match(pattern: $this->urlRegexp, subject: $blockquote, matches: $matches);
        if ($result !== 1) {
            throw new RuntimeException('Failed finding URL in blockquote ' . $blockquote);
        }

        return $matches[0];
    }

    private function getMatchedParensWrappedUrl(string $blockquote): ?string
    {
        $result = preg_match(pattern: $this->getRegexForParensWrappedUrl(), subject: $blockquote, matches: $matches);
        if ($result !== 1) {
            return null;
        }

        return $matches[0];
    }

    private function getRegexForParensWrappedUrl(): string
    {
        $trimmedRegexp = trim($this->urlRegexp, '%');
        if ($trimmedRegexp === $this->urlRegexp) {
            throw new RuntimeException('Invalid delimiters used for regexp, you must use % delim');
        }

        return self::REGEX_LINK_LOOKBEHIND . $trimmedRegexp . self::REGEX_LINK_LOOKAHEAD;
    }
}
