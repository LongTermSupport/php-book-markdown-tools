<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test\MarkdownProcessor;

use LTS\MarkdownTools\MarkdownProcessor\Factory;
use LTS\MarkdownTools\MarkdownProcessor\RunConfig;
use LTS\MarkdownTools\Test\TestHelper;
use PHPUnit\Framework\TestCase;

/**
 * This test simulates teh full system being run.
 * We run it multiple times to ensure that the system is idempotent.
 *
 * @internal
 * @coversNothing
 * @small
 */
final class IntegrationTest extends TestCase
{
    private const WORKING_DIR        = TestHelper::VAR_PATH . '/IntegrationTest';
    private const TEST_CHAPTERS_DIR  = self::WORKING_DIR . TestHelper::CHAPTER_SUB_PATH;
    private const TEST_CHAPTER1_PATH = self::WORKING_DIR . TestHelper::CHAPTER1_SUB_PATH;
    private const TIMES_TO_RUN       = 4;
    private const EXPECTED           = <<<'MARKDOWN'
# kjhasdkjh kwjer kjhs adkh kwer

lkjhb lkALSKJH KJAHSD KJASD

> ###### PHP: Autoloading Classes - Manual 
> https://www.php.net/manual/en/language.oop5.autoload.php

asdasd asd

> asdkjh aksjhd khjasd
> ljhaskdjh kasjhd kjhasd

[Code Snippet](./../../../Code/Bang/Bong/Blah.php)

```php
<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Test\Fixture\Code\Bang\Bong;

$foo       = 'boo';
$boo       = 'foo';
const BLAH = 'blah';

```

kjha skjdh kjhaskdjh kjhaskdjh kjas kdjh asd kljhkljasd kjh kajsd

[Code Executable Snippet](./../../../Code/Bang/Bong/Boo.php)

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

    public function setUp(): void
    {
        TestHelper::setupFixtures(self::WORKING_DIR);
    }

    /** @test */
    public function processFull(): void
    {
        $config    = new RunConfig(self::TEST_CHAPTERS_DIR, TestHelper::CACHE_PATH);
        $processor = Factory::create($config);
        for ($i = 0; $i < self::TIMES_TO_RUN; ++$i) {
            $processor->run($config);
            self::assertSame(
                expected: self::EXPECTED,
                actual: \Safe\file_get_contents(self::TEST_CHAPTER1_PATH),
                message: 'Tests failed on run ' . $i . ' of ' . self::TIMES_TO_RUN
            );
        }
    }
}
