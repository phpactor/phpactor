<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Generator;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\Exception\ClassNotFound;
use Phpactor\TestUtils\ExtractOffset;

class SourceReflectorTest extends IntegrationTestCase
{
    #[DataProvider('provideReflectClassNotCorrectType')]
    public function testReflectClassNotCorrectType(string $source, string $class, string $method, string $expectedErrorMessage): void
    {
        $source = TextDocumentBuilder::fromUnknown($source);
        $this->expectException(ClassNotFound::class);
        $this->expectExceptionMessage($expectedErrorMessage);

        $this->createReflector($source)->$method($class);
    }

    /**
     * @return Generator<string,array{string,string,string,string}>
     */
    public static function provideReflectClassNotCorrectType(): Generator
    {
        yield 'Class' => [
            '<?php trait Foobar {}',
            'Foobar',
            'reflectClass',
            '"Foobar" is not a class',
        ];
        yield 'Interface' => [
            '<?php class Foobar {}',
            'Foobar',
            'reflectInterface',
            '"Foobar" is not an interface',
        ];
        yield 'Trait' => [
            '<?php interface Foobar {}',
            'Foobar',
            'reflectTrait',
            '"Foobar" is not a trait',
        ];
    }

    #[TestDox('It reflects the value at an offset.')]
    public function testReflectOffset(): void
    {
        $source = <<<'EOT'
            <?php

            $foobar = 'Hello';
            $foobar;
            EOT
        ;

        $source = TextDocumentBuilder::fromUnknown($source);
        $offset = $this->createReflector($source)->reflectOffset($source, 27);
        $this->assertEquals('"Hello"', (string) $offset->nodeContext()->type());
    }

    #[TestDox('It reflects the value at an offset.')]
    public function testReflectOffsetRedeclared(): void
    {
        $source = <<<'EOT'
            <?php

            $foobar = 'Hello';
            $foobar = 1234;
            $foob<>ar;
            EOT
        ;

        [$source, $offset] = ExtractOffset::fromSource($source);

        $source = TextDocumentBuilder::fromUnknown($source);
        $offset = $this->createReflector($source)->reflectOffset($source, (int)$offset);
        $this->assertEquals('1234', (string) $offset->nodeContext()->type());
    }
}
