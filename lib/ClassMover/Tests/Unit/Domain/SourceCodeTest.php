<?php

namespace Phpactor\ClassMover\Tests\Unit\Domain;

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

    /** @return array<array{string, string}> */
    public function provideAddUse(): array
    {
        return [
            'No namespace' => [
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
            ],
            'Namespace, no use statements' => [
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
            ],
            'Use statements' => [
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
            ]
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

    /** @return array<array{string, string}> */
    public function provideNamespaceAdd(): array
    {
        return [
            'Add namespace' => [
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
            ],
            'Ignore existing' => [
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
            ],
            'Ignore no tag' => [
                <<<'EOT'
                    class
                    EOT
                ,
                <<<'EOT'
                    class
                    EOT
            ]
        ];
    }
}
