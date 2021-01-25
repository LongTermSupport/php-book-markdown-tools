<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Process\BlockQuote;

use RuntimeException;

final class DocsLinkProcess implements BlockQuoteProcess
{
    private const URL_REGEXP = <<<'REGEXP'
%https://www.php.net/.+%
REGEXP;

    private const HEADER_REGEXP = <<<'REGEXP'
%<h(?<level>\d).*?>(?<heading>[^<]+)</h%
REGEXP;

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

    private function getHeadings(string $content): string
    {
        preg_match_all(pattern: self::HEADER_REGEXP, subject: $content, matches: $matches);
        $return = '';
        foreach ($matches['heading'] as $k => $heading) {
            $level  = $matches['level'][$k];
            $return .= '>' . str_repeat(' ', (int)$level) . '* ' . $heading . "\n";
        }

        return $return;
    }

    private function getTitle(string $content): string
    {
        preg_match('%<title>([^<]+?)</%', $content, $matches);

        return $matches[1];
    }

    private function buildBlock(string $url): string
    {
        $content  = \Safe\file_get_contents($url);
        $title    = $this->getTitle($content);
        $headings = $this->getHeadings($content);

        return <<<MARKDOWN
> {$title}
> {$url}
{$headings}
MARKDOWN;
    }
}
