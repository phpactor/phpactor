<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core;

use Phpactor\TextDocument\ByteOffset;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\Exception\ClassNotFound;
use Phpactor\TestUtils\ExtractOffset;

class SourceReflectorTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectClassNotCorrectType
     */
    public function testReflectClassNotCorrectType(string $source, string $class, string $method, string $expectedErrorMessage): void
    {
        $this->expectException(ClassNotFound::class);
        $this->expectExceptionMessage($expectedErrorMessage);

        $this->createReflector($source)->$method($class);
    }

    public function provideReflectClassNotCorrectType()
    {
        return [
            'Class' => [
                '<?php trait Foobar {}',
                'Foobar',
                'reflectClass',
                '"Foobar" is not a class',
            ],
            'Interface' => [
                '<?php class Foobar {}',
                'Foobar',
                'reflectInterface',
                '"Foobar" is not an interface',
            ],
            'Trait' => [
                '<?php interface Foobar {}',
                'Foobar',
                'reflectTrait',
                '"Foobar" is not a trait',
            ]
        ];
    }

    /**
     * @testdox It reflects the value at an offset.
     */
    public function testReflectOffset(): void
    {
        $source = <<<'EOT'
            <?php

            $foobar = 'Hello';
            $foobar;
            EOT
        ;

        $offset = $this->createReflector($source)->reflectOffset($source, 27);
        $this->assertEquals('"Hello"', (string) $offset->nodeContext()->type());
    }

    /**
     * @testdox It reflects the value at an offset.
     */
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

        $offset = $this->createReflector($source)->reflectOffset($source, (int)$offset);
        $this->assertEquals('1234', (string) $offset->nodeContext()->type());
    }
}
