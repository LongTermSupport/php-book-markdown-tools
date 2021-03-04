<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\DocumentCreator\MarkdownPreprocess\Process;

use LTS\MarkdownTools\DocumentCreator\MarkdownPreprocess\RunConfig;
use LTS\MarkdownTools\ProcessorInterface;
use LTS\MarkdownTools\Util\Helper;
use LTS\MarkdownTools\Util\LinkShortener\GithubLinkShortener;
use RuntimeException;

final class ConvertCodeSnippetLinkProcess implements ProcessorInterface
{
    private const CODE_SNIPPET_LINK_REGEXP = <<<'REGEXP'
%^\[Code.+Snippet]\((?<file_path>[^)]+?)\)%m
REGEXP;

    public function __construct(private RunConfig $runConfig, private GithubLinkShortener $githubLinkShortener)
    {
    }

    public function getProcessedContents(string $currentContents, string $currentFileDir): string
    {
        \Safe\preg_match_all(self::CODE_SNIPPET_LINK_REGEXP, $currentContents, $matches);
        foreach ($matches[0] as $index => $match) {
            $filePath = $matches['file_path'][$index];
            $replace  = $this->processMatch($currentContents, $currentFileDir, $filePath);
            if ($replace === null) {
                continue;
            }
            $currentContents = str_replace($match, $replace, $currentContents);
        }

        return $currentContents;
    }

    private function processMatch(string $currentContents, string $currentFileDir, string $filePath): ?string
    {
        if (str_starts_with($filePath, './') === true) {
            return $this->processLocalFile($currentContents, $currentFileDir, $filePath);
        }
        if ($this->githubLinkShortener->canShorten($filePath) === true) {
            return $this->githubLinkShortener->getShortenedLinkMarkDown($filePath);
        }

        return null;
    }

    private function processLocalFile(string $currentContents, string $currentFileDir, string $filePath): string
    {
        $githubUrl = $this->getGithubUrlFromLocalFilePath($currentFileDir, $filePath);

        return $this->githubLinkShortener->getShortenedLinkMarkDown($githubUrl);
    }

    private function getGithubUrlFromLocalFilePath(string $currentFileDir, string $filePath): string
    {
        $resolvedPath = Helper::resolveRelativePath("{$currentFileDir}/{$filePath}");
        $realpath     = \Safe\realpath($resolvedPath);
        $localBase    = $this->runConfig->getLocalRepoBasePath();
        if (str_starts_with($realpath, $localBase) === false) {
            throw new RuntimeException('Unexpected path ' . $realpath);
        }
        $relativePath = str_replace($localBase, '', $realpath);

        return Helper::removeDuplicateUrlSlashes($this->runConfig->getGithubRepoBaseUrl() . $relativePath);
    }
}
