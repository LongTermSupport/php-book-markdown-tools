<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\MarkdownProcessor\Process\CodeSnippet;

use LTS\MarkdownTools\MarkdownProcessor\CachingUrlFetcher;
use RuntimeException;

final class GithubCodeSnippetProcess implements CodeSnippetProcessInterface
{
    public const GITHUB_URL_REGEX   = '%https://github.com/(?<ns>.+?)/(?<name>.+?)/(?<raw_or_not>.+?)/(?<path>.+)%';
    private const GITHUB_RAW_FORMAT = 'https://github.com/%s/%s/raw/%s';

    public function __construct(private CachingUrlFetcher $urlFetcher)
    {
    }

    public function getProcessedReplacement(string $githubUrl, string $snippetType, string $currentFileDir): string
    {
        if ($snippetType !== self::STANDARD_TYPE) {
            throw new RuntimeException('Github snippets can only be standard');
        }
        $code = $this->getRawContents($githubUrl);

        return sprintf(self::REPLACE_FORMAT, $snippetType, $githubUrl, $code);
    }

    public function shouldProcess(string $filePath): bool
    {
        return preg_match(self::GITHUB_URL_REGEX, $filePath) === 1;
    }

    private function getRawContents(string $githubUrl): string
    {
        $url = $this->getRawUrl($githubUrl);

        return $this->urlFetcher->getContents($url);
    }

    private function getRawUrl(string $githubUrl): string
    {
        if (\Safe\preg_match(self::GITHUB_URL_REGEX, $githubUrl, $matches) !== 1) {
            throw new RuntimeException('Failed matching ' . $githubUrl);
        }

        return sprintf(self::GITHUB_RAW_FORMAT, $matches['ns'], $matches['name'], $matches['path']);
    }
}
