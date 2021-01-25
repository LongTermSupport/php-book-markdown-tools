<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Process;

use LTS\MarkdownTools\Process\BlockQuote\BlockQuoteProcess;
use LTS\MarkdownTools\ProcessorInterface;

final class BlockQuoteProcessor implements ProcessorInterface
{
    # lifted from https://github.com/michelf/php-markdown/blob/6975244af21bd467235217813f3473bf3929a208/Michelf/Markdown.php#L1441
    private const PHP_DOC_LINK_REGEXP = <<<'REGEXP'
/
(?<blockquote>			# Wrap whole match in $1
(?>
  ^[ ]*>[ ]?			# ">" at the start of a line
    .+\n				# rest of the first line
  (.+\n)*				# subsequent consecutive lines
  \n*					# blanks
)+
)
/xm
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
            $processedBlockQuote = $this->ensureEndsIn2LineBreaks($processedBlockQuote);
            $currentContents     = str_replace(
                search: $origBlockQuote,
                replace: $processedBlockQuote,
                subject: $currentContents
            );
        }

        return $currentContents;
    }

    private function ensureEndsIn2LineBreaks(string $blockQuote): string
    {
        if ("\n\n" === substr($blockQuote, -2)) {
            return $blockQuote;
        }

        return "$blockQuote\n\n";
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
