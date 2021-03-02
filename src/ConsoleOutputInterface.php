<?php

declare(strict_types=1);

namespace LTS\MarkdownTools;

interface ConsoleOutputInterface
{
    public function stdErr(string $message): void;

    public function stdOut(string $message): void;
}
