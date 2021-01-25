<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Process;

use LTS\MarkdownTools\Process\BlockQuote\BlockQuoteProcess;
use LTS\MarkdownTools\ProcessorInterface;

final class BlockQuoteProcessor implements ProcessorInterface
{
    private const PHP_DOC_LINK_REGEXP = <<<REGEXP
%(?<blockquote>^> ?.+?)((\r?\n\r?\n\\w)|\\Z)%sm
REGEXP;
    /**
     * @var BlockQuoteProcess[]
     */
    private array $processors;

    public function __construct(BlockQuoteProcess ...$processors)
    {
        $this->processors = $processors;
    }

    public function getProcessedContents(string $currentContents, string $currentFileDir): string
    {
        preg_match_all(pattern: self::PHP_DOC_LINK_REGEXP, subject: $currentContents, matches: $matches);
        foreach ($matches['blockquote'] as $origBlockQuote) {
            $processedBlockQuote = $this->processBlockQuote($origBlockQuote);
            $currentContents     = str_replace(
                search: $origBlockQuote,
                replace: $processedBlockQuote,
                subject: $currentContents
            );
        }

        return $currentContents;
    }

    private function processBlockQuote(string $blockQuote): string
    {
        foreach ($this->processors as $blockQuoteProcessor) {
            if ($blockQuoteProcessor->shouldProcess($blockQuote)) {
                $blockQuote = $blockQuoteProcessor->processBlockQuote($blockQuote);
            }
        }

        return $blockQuote;
    }
}
