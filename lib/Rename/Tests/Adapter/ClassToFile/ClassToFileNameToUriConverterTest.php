<?php

namespace Phpactor\Rename\Tests\Adapter\ClassToFile;

use Phpactor\ClassFileConverter\Adapter\Simple\SimpleClassToFile;
use Phpactor\Rename\Adapter\ClassToFile\ClassToFileNameToUriConverter;
use Phpactor\Extension\LanguageServerRename\Tests\IntegrationTestCase;
use Phpactor\TextDocument\TextDocumentUri;

class ClassToFileNameToUriConverterTest extends IntegrationTestCase
{
    public function testConvert(): void
    {
        $this->workspace()->put('Foo.php', '<?php class Foo {}');

        $converter = new ClassToFileNameToUriConverter(new SimpleClassToFile($this->workspace()->path()));

        $uri = $converter->convert('Foo');

        self::assertInstanceOf(TextDocumentUri::class, $uri);
        self::assertEquals($this->workspace()->path('Foo.php'), $uri->path());
    }
}
