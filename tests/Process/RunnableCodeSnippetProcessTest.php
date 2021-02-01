<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test\Process;

use LTS\MarkdownTools\Process\RunnableCodeSnippetProcess;
use LTS\MarkdownTools\Test\TestHelper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \LTS\MarkdownTools\Process\RunnableCodeSnippetProcess
 *
 * @small
 */
final class RunnableCodeSnippetProcessTest extends TestCase
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

    public static function getProcessor(): RunnableCodeSnippetProcess
    {
        return new RunnableCodeSnippetProcess();
    }

    /** @test */
    public function itCanGetAndRunCodeSnippets(): void
    {
        TestHelper::createTestFile(contents: self::TEST_CODE_RUNNABLE, filename: __FUNCTION__ . '.php');
        $mdContents = \Safe\sprintf(self::MARKDOWN_SNIPPET, './' . __FUNCTION__ . '.php');
        $actual     = self::getProcessor()->getProcessedContents($mdContents, TestHelper::VAR_PATH);
        $expected   = '# some content blah

[Code Snippet](./itCanGetAndRunCodeSnippets.php)

```php
<?php 

$foo=1;
$bar=2;

function add(int $a, int $b):int{
    return $a+$b;
}echo "And new we add some stuff";
echo add($foo, $bar);

?>

OUTPUT:

And new we add some stuff3

```';
        self::assertSame($expected, $actual);
    }
}
