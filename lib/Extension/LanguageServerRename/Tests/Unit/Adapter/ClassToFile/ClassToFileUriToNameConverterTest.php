<?php

namespace Phpactor\Extension\LanguageServerRename\Tests\Unit\Adapter\ClassToFile;

use Phpactor\ClassFileConverter\Adapter\Simple\SimpleFileToClass;
use Phpactor\Extension\LanguageServerRename\Adapter\ClassToFile\ClassToFileUriToNameConverter;
use Phpactor\Extension\LanguageServerRename\Model\Exception\CouldNotConvertUriToClass;
use Phpactor\Extension\LanguageServerRename\Tests\IntegrationTestCase;
use Phpactor\TextDocument\TextDocumentUri;

class ClassToFileUriToNameConverterTest extends IntegrationTestCase
{
    public function testConvert(): void
    {
        $this->workspace()->put('1.php', '<?php class Foo {}');

        $converter = new ClassToFileUriToNameConverter(new SimpleFileToClass());
        $class = $converter->convert(TextDocumentUri::fromString($this->workspace()->path('1.php')));
        self::assertEquals('Foo', $class);
    }

    public function testErrorWhenCannotConvert(): void
    {
        $this->expectException(CouldNotConvertUriToClass::class);
        $this->workspace()->put('1.php', '<?php ');

        $converter = new ClassToFileUriToNameConverter(new SimpleFileToClass());
        $converter->convert(TextDocumentUri::fromString($this->workspace()->path('1.php')));
    }
}
