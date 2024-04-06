<?php

namespace Phpactor\ClassMover\Tests\Unit\Domain;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\ClassMover\Domain\SourceCode;
use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;

class SourceCodeTest extends TestCase
{
    /**
     * It should add a use statement.
     * @dataProvider provideAddUse
     */
    public function testAddUse(string $source, string $expected): void
    {
        $source = SourceCode::fromString($source);
        $source = $source->addUseStatement(FullyQualifiedName::fromString('Foobar'));
        $this->assertEquals($expected, $source->__toString());
    }

    /** @return Generator<array{string, string}> */
    public function provideAddUse(): Generator
    {
        yield 'No namespace' => [
            <<<'EOT'
                <?php

                class
                EOT
            ,
            <<<'EOT'
                <?php

                use Foobar;

                class
                EOT
        ];
        yield 'Namespace, no use statements' => [
            <<<'EOT'
                <?php

                namespace Acme;

                class
                EOT
            ,
            <<<'EOT'
                <?php

                namespace Acme;

                use Foobar;

                class
                EOT
        ];
        yield 'Use statements' => [
            <<<'EOT'
                <?php

                namespace Acme;

                use Acme\BarBar;

                class
                EOT
            ,
            <<<'EOT'
                <?php

                namespace Acme;

                use Acme\BarBar;
                use Foobar;

                class
                EOT
        ];
    }

    /**
     * @dataProvider provideNamespaceAdd
     */
    public function testNamespaceAdd(string $source, string $expected): void
    {
        $source = SourceCode::fromString($source);
        $source = $source->addNamespace(FullyQualifiedName::fromString('NS1'));
        $this->assertEquals($expected, $source->__toString());
    }

    /**
     * @return Generator<array{string, string}>
     */
    public function provideNamespaceAdd(): Generator
    {
        yield 'Add namespace' => [
            <<<'EOT'
                <?php

                class
                EOT
            ,
            <<<'EOT'
                <?php

                namespace NS1;

                class
                EOT
        ];
        yield 'Ignore existing' => [
            <<<'EOT'
                <?php

                namespace NS1;

                class
                EOT
            ,
            <<<'EOT'
                <?php

                namespace NS1;

                class
                EOT
        ];
        yield 'Ignore no tag' => [
            <<<'EOT'
                class
                EOT
            ,
            <<<'EOT'
                class
                EOT
        ];
    }
}
