<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test\DocumentCreator\MarkdownPreprocess\Process;

use LTS\MarkdownTools\ConsoleOutput;
use LTS\MarkdownTools\DocumentCreator\MarkdownPreprocess\Process\CodeFenceToImageProcess;
use LTS\MarkdownTools\DocumentCreator\MarkdownPreprocess\RunConfig;
use LTS\MarkdownTools\Test\TestHelper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \LTS\MarkdownTools\DocumentCreator\MarkdownPreprocess\Process\CodeFenceToImageProcess
 *
 * @medium
 */
final class CodeFenceToImageProcessTest extends TestCase
{
    private const TEST_DIR      = TestHelper::VAR_PATH . 'CodeFenceToImageProcessTest/';
    private const CHAPTER1_PATH = self::TEST_DIR . TestHelper::CHAPTER1_SUB_PATH;
    private const CODE_PATH     = self::TEST_DIR . TestHelper::CODE_SUB_PATH;
    private const EXPECTED      = <<<'MARKDOWN'
# kjhasdkjh kwjer kjhs adkh kwer

lkjhb lkALSKJH KJAHSD KJASD

> ###### PHP: Autoloading Classes - Manual 
> https://www.php.net/manual/en/language.oop5.autoload.php

asdasd asd

> asdkjh aksjhd khjasd
> ljhaskdjh kasjhd kjhasd

[Code Snippet](./../../../Code/Bang/Bong/Blah.php)

![](./generated-images/acdac3c66e65799f9a7212db8f8a8f71.png)

kjha skjdh kjhaskdjh kjhaskdjh kjas kdjh asd kljhkljasd kjh kajsd

[Code Executable Snippet](./../../../Code/Bang/Bong/Boo.php)

![](./generated-images/ab65ef0a3c393a9a17626a6abb0eb70d.png)

###### Output:
![](./generated-images/8941ac9f5b6279ffcdd581854eadccc0.png)

kjh weiurh iuuihjksadhj hasd
MARKDOWN;

    private CodeFenceToImageProcess $process;

    public function setUp(): void
    {
        $config        = TestHelper::setupProcessedFixtures(self::TEST_DIR);
        $this->process = new CodeFenceToImageProcess(
            new RunConfig(
                githubRepoBaseUrl: 'https://github.com/foo/bar/blob/master/',
                localRepoBasePath: self::CODE_PATH,
                pathToChapters: $config->getPathToChapters(),
                convertCodeToImage: true,
                convertOutputToTerminalImage: true
            ),
            new ConsoleOutput()
        );
    }

    /** @test */
    public function itConvertsFencesToImages(): void
    {
        $chapterDir = dirname(self::CHAPTER1_PATH);
        $actual     = $this->process->getProcessedContents(
            \Safe\file_get_contents(self::CHAPTER1_PATH),
            $chapterDir
        );
        $imagePath  = $chapterDir . CodeFenceToImageProcess::CHAPTER_IMAGE_FOLDER;
        self::assertDirectoryExists($imagePath);
        self::assertCount(3, (array)glob("{$imagePath}/*.png"));
        self::assertSame(trim(self::EXPECTED), trim($actual));
    }
}
