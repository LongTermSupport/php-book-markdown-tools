<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test\DocumentCreator\MarkdownPreprocess\Process;

use LTS\MarkdownTools\ConsoleOutput;
use LTS\MarkdownTools\DocumentCreator\MarkdownPreprocess\Process\CodeFenceToImageProcess;
use LTS\MarkdownTools\Test\TestHelper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 *
 * @medium
 */
final class CodeFenceToImageProcessTest extends TestCase
{
    private const TEST_FILE       = 'Chapter1-Expected.md';
    private const FIXTURE_PATH    = TestHelper::FIXTURE_PATH . self::TEST_FILE;
    private const TEST_DIR        = TestHelper::VAR_PATH . 'CodeFenceToImageProcessTest/';
    private const TEST_PATH       = self::TEST_DIR . self::TEST_FILE;
    private const TEST_IMAGE_PATH = self::TEST_DIR . CodeFenceToImageProcess::CHAPTER_IMAGE_FOLDER;
    private CodeFenceToImageProcess $process;

    public function setUp(): void
    {
        TestHelper::nuke();
        TestHelper::createVarDir(self::TEST_DIR);
        $this->process = new CodeFenceToImageProcess(new ConsoleOutput());
        \Safe\file_put_contents(
            self::TEST_PATH,
            \Safe\file_get_contents(self::FIXTURE_PATH)
        );
    }

    /** @test */
    public function itConvertsFencesToImages(): void
    {
        $expected = '# kjhasdkjh kwjer kjhs adkh kwer

lkjhb lkALSKJH KJAHSD KJASD

> ###### PHP: Autoloading Classes - Manual 
> https://www.php.net/manual/en/language.oop5.autoload.php

asdasd asd 

> asdkjh aksjhd khjasd
> ljhaskdjh kasjhd kjhasd

[Code Snippet](./../../Bing/Bang/Bong/Blah.php)

![](./generated-images/0b0e7076ab7e7b0c7afd920259adf88b.png)

kjha skjdh kjhaskdjh 
kjhaskdjh kjas kdjh asd
kljhkljasd kjh kajsd

[Code Executable Snippet](./../../Bing/Bang/Bong/Boo.php)

![](./generated-images/c1ab54cfb56e77a014b3219034480905.png)

###### Output:
![](./generated-images/8941ac9f5b6279ffcdd581854eadccc0.png)

kjh weiurh iuuihjksadhj hasd';
        $actual   = $this->process->getProcessedContents(
            \Safe\file_get_contents(self::TEST_PATH),
            self::TEST_DIR
        );
        self::assertDirectoryExists(self::TEST_IMAGE_PATH);
        self::assertSame(trim($expected), trim($actual));
    }
}
