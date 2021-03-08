<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\DocumentCreator\MarkdownPreprocess;

use InvalidArgumentException;
use LTS\MarkdownTools\Config\PathToChaptersConfigInterface;
use RuntimeException;
use Safe\Exceptions\FilesystemException;

final class RunConfig implements PathToChaptersConfigInterface
{
    public const VAR_PATH = __DIR__ . '/../../../var/';

    /**
     * @param string $githubRepoBaseUrl - The Base URL for files within the repo, eg
     *                                  https://github.com/LongTermSupport/php-book-code/blob/master/
     * @param string $localRepoBasePath - The base absolute path for the local repo, eg
     *                                  /home/book/php-book-code/
     */
    public function __construct(
        private string $githubRepoBaseUrl,
        private string $localRepoBasePath,
        private string $pathToChapters,
        private bool $convertCodeToImage = false,
        private bool $convertOutputToTerminalImage = true,
        private ?string $cachePath = null
    ) {
        $this->assertValidGithubUrl();
        try {
            $this->localRepoBasePath = \Safe\realpath($this->localRepoBasePath);
        } catch (FilesystemException $exception) {
            throw new RuntimeException(
                'Failed getting realpath for ' . $this->localRepoBasePath,
                $exception->getCode(),
                $exception
            );
        }
    }

    public function getCachePath(): ?string
    {
        return $this->cachePath;
    }

    public function getPathToChapters(): string
    {
        return $this->pathToChapters;
    }

    public function getGithubRepoBaseUrl(): string
    {
        return $this->githubRepoBaseUrl;
    }

    public function getLocalRepoBasePath(): string
    {
        return $this->localRepoBasePath;
    }

    public function isConvertCodeToImage(): bool
    {
        return $this->convertCodeToImage;
    }

    public function isConvertOutputToTerminalImage(): bool
    {
        return $this->convertOutputToTerminalImage;
    }

    private function assertValidGithubUrl(): void
    {
        if (str_contains($this->githubRepoBaseUrl, '/blob/') === false) {
            throw new InvalidArgumentException('Github url ' . $this->githubRepoBaseUrl . 'does not include /blob/');
        }
    }
}
