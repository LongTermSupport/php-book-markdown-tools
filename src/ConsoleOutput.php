<?php

declare(strict_types=1);

namespace LTS\MarkdownTools;

final class ConsoleOutput implements ConsoleOutputInterface
{
    public function stdErr(string $message): void
    {
        fwrite(STDERR, "\n err: {$message}");
    }

    public function stdOut(string $message): void
    {
        fwrite(STDOUT, "\n {$message}");
    }
}
