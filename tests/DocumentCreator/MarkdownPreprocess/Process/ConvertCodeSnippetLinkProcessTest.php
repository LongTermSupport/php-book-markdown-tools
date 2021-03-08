<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test\DocumentCreator\MarkdownPreprocess\Process;

use LTS\MarkdownTools\DocumentCreator\MarkdownPreprocess\Process\ConvertCodeSnippetLinkProcess;
use LTS\MarkdownTools\DocumentCreator\MarkdownPreprocess\RunConfig;
use LTS\MarkdownTools\Test\TestHelper;
use LTS\MarkdownTools\Util\Helper;
use LTS\MarkdownTools\Util\LinkShortener\GithubLinkShortener;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \LTS\MarkdownTools\DocumentCreator\MarkdownPreprocess\Process\ConvertCodeSnippetLinkProcess
 *
 * @medium
 */
final class ConvertCodeSnippetLinkProcessTest extends TestCase
{
    private const TEST_DIR  = TestHelper::VAR_PATH . 'ConvertCodeSnippetLinkProcess/';
    private const TEST_FILE = self::TEST_DIR . TestHelper::CHAPTER1_SUB_PATH;
    private const EXPECTED  = <<<'MARKDOWN'
# kjhasdkjh kwjer kjhs adkh kwer

lkjhb lkALSKJH KJAHSD KJASD

> ###### PHP: Autoloading Classes - Manual 
> https://www.php.net/manual/en/language.oop5.autoload.php

asdasd asd

> asdkjh aksjhd khjasd
> ljhaskdjh kasjhd kjhasd


> 
> ###### var/tests/ConvertCodeSnippetLinkProcess/Fixture/Code/Bang/Bong/Blah.php 
> **Repo:** [https://git.io/Jqf6B](https://github.com/LongTermSupport/php-book-markdown-tools/blob/main/var/tests/ConvertCodeSnippetLinkProcess/Fixture/Code/Bang/Bong/Blah.php)        

```php
<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test\Fixture\Code\Bang\Bong;

$foo       = 'boo';
$boo       = 'foo';
const BLAH = 'blah';

```

kjha skjdh kjhaskdjh kjhaskdjh kjas kdjh asd kljhkljasd kjh kajsd


> 
> ###### var/tests/ConvertCodeSnippetLinkProcess/Fixture/Code/Bang/Bong/Boo.php 
> **Repo:** [https://git.io/Jqf6R](https://github.com/LongTermSupport/php-book-markdown-tools/blob/main/var/tests/ConvertCodeSnippetLinkProcess/Fixture/Code/Bang/Bong/Boo.php)        

```php
<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test\Fixture\Code\Bang\Bong;

echo 'This, that, the other';

```

###### Output:
```terminal php Boo.php

This, that, the other

```

kjh weiurh iuuihjksadhj hasd
MARKDOWN;
    private ConvertCodeSnippetLinkProcess $process;

    public function setUp(): void
    {
        TestHelper::setupProcessedFixtures(self::TEST_DIR);
        $this->process = new ConvertCodeSnippetLinkProcess(
            new RunConfig(
                githubRepoBaseUrl: 'https://github.com/LongTermSupport/php-book-markdown-tools/blob/main/',
                localRepoBasePath: Helper::resolveRelativePath(TestHelper::PROJECT_ROOT_PATH),
                pathToChapters: self::TEST_DIR . TestHelper::CHAPTER_SUB_PATH
            ),
            new GithubLinkShortener(TestHelper::getCache())
        );
    }

    /** @test */
    public function itConvertsLocalPathsToGithubUrls(): void
    {
        $actual = $this->process->getProcessedContents(
            \Safe\file_get_contents(self::TEST_FILE),
            dirname(self::TEST_FILE)
        );
        self::assertSame(trim(self::EXPECTED), trim($actual));
    }
}
