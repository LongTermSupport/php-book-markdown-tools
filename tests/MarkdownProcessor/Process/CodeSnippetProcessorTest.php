<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test\MarkdownProcessor\Process;

use LTS\MarkdownTools\ConsoleOutputInterface;
use LTS\MarkdownTools\MarkdownProcessor\CachingUrlFetcher;
use LTS\MarkdownTools\MarkdownProcessor\Process\CodeSnippet\GithubCodeSnippetProcess;
use LTS\MarkdownTools\MarkdownProcessor\Process\CodeSnippet\LocalCodeSnippetProcess;
use LTS\MarkdownTools\MarkdownProcessor\Process\CodeSnippetProcessor;
use LTS\MarkdownTools\Test\TestHelper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \LTS\MarkdownTools\MarkdownProcessor\Process\CodeSnippetProcessor
 *
 * @small
 */
final class CodeSnippetProcessorTest extends TestCase
{
    public const TEST_CODE = <<<'CODE'
<?php 

$foo=1;
$bar=2;

function add(int $a, int $b):int{
    return $a+$b;
}
CODE;

    public const TEST_CODE_RUNNABLE = self::TEST_CODE . <<<'CODE'
echo "And new we add some stuff";
echo add($foo, $bar);
CODE;

    public const MARKDOWN_SNIPPET = <<<'MARKDOWN'
# some content blah

[Code Snippet](%s)

```php
some previous contents here
```
MARKDOWN;

    public const MARKDOWN_EXECUTABLE_SNIPPET = <<<'MARKDOWN'
# some content blah

[Code Executable Snippet](%s)

```php
some previous contents here
```

blah blahb fkjhkjasd
asdkljhkjsadkjhasd
MARKDOWN;

    /** @test */
    public function itCanGetCodeSnippets(): void
    {
        TestHelper::createTestFile(contents: self::TEST_CODE, filename: __FUNCTION__ . '.php');
        $mdContents = \Safe\sprintf(self::MARKDOWN_SNIPPET, './' . __FUNCTION__ . '.php');
        $actual     = self::getProcessor()->getProcessedContents($mdContents, TestHelper::VAR_PATH);
        $expected   = '# some content blah

[Code Snippet](./itCanGetCodeSnippets.php)

```php
' . self::TEST_CODE . '
```';
        self::assertSame($expected, $actual);
    }

    public static function getProcessor(): CodeSnippetProcessor
    {
        return new CodeSnippetProcessor(
            new class() implements ConsoleOutputInterface {
                public function stdErr(string $message): void
                {
                }

                public function stdOut(string $message): void
                {
                }
            },
            new LocalCodeSnippetProcess(),
            new GithubCodeSnippetProcess(new CachingUrlFetcher())
        );
    }

    /** @test */
    public function itCanGetAndRunCodeSnippets(): void
    {
        TestHelper::createTestFile(contents: self::TEST_CODE_RUNNABLE, filename: __FUNCTION__ . '.php');
        $mdContents = \Safe\sprintf(self::MARKDOWN_EXECUTABLE_SNIPPET, './' . __FUNCTION__ . '.php');
        for ($i = 1; $i < 4; ++$i) {
            $mdContents = self::getProcessor()->getProcessedContents($mdContents, TestHelper::VAR_PATH);
            $expected   = '# some content blah

[Code Executable Snippet](./itCanGetAndRunCodeSnippets.php)

```php
<?php 

$foo=1;
$bar=2;

function add(int $a, int $b):int{
    return $a+$b;
}echo "And new we add some stuff";
echo add($foo, $bar);
```

###### Output:
```terminal php itCanGetAndRunCodeSnippets.php

And new we add some stuff3

```

blah blahb fkjhkjasd
asdkljhkjsadkjhasd';
            self::assertSame($expected, $mdContents, 'Failed at iteration ' . $i);
        }
    }
}
