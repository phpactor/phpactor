<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Helper;

use Generator;
use Phpactor\CodeTransform\Adapter\WorseReflection\Helper\WorseMissingMethodFinder;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use function Amp\Promise\wait;

class WorseMissingMethodFinderTest extends WorseTestCase
{
    /**
     * @dataProvider provideFindMissingMethods
     */
    public function testFindMissingMethods(string $source, int $expectedCount): void
    {
        [$source, $offset] = ExtractOffset::fromSource($source);
        $reflector = $this->reflectorForWorkspace($source);
        $document = TextDocumentBuilder::create($source)->uri('file:///test')->build();

        $methods = wait((new WorseMissingMethodFinder($reflector))->find($document));
        self::assertCount($expectedCount, $methods);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideFindMissingMethods(): Generator
    {
        yield 'no methods' => [
            <<<'EOT'
                <?php
                class foobar { }
                EOT
            , 0
        ];
        yield 'no missing methods' => [
            <<<'EOT'
                <?php
                class foobar { function foo() {} public function bar() { $this->foo(); } }
                EOT
            , 0
        ];
        yield '1 missing method' => [
            <<<'EOT'
                <?php
                class foobar { public function bar() { $this->foo(); } }
                EOT
            , 1
        ];
        yield 'missing static method' => [
            <<<'EOT'
                <?php
                class foobar { public function bar() { self::foo(); } }
                EOT
            , 1
        ];
        yield 'present static method' => [
            <<<'EOT'
                <?php
                class foobar { public static function foo() { self::foo(); } }
                EOT
            , 0
        ];
        yield 'dynamic method call (not supported)' => [
            <<<'EOT'
                <?php
                class foobar { public static function foo() { self::$foo(); } }
                EOT
            , 0
        ];
        yield 'call foreign class missing' => [
            <<<'EOT'
                <?php
                class Bar () { function zed() {} }
                $new = new Bar();
                $new->bof();
                EOT
            , 1
        ];
        yield 'call foreign class present' => [
            <<<'EOT'
                <?php
                class Bar { function zed() {} }
                $new = new Bar();
                $new->zed();
                EOT
            , 0
        ];
        yield 'methods from trait' => [
            <<<'EOT'
                <?php
                trait Baz { public function boo(): void }
                {
                }
                class Bar { use Baz; function zed() {} }
                $new = new Bar();
                $new->boo();
                EOT
            , 0
        ];
        yield 'methods from trait with virtual method' => [
            <<<'EOT'
                <?php

                /**
                 * @method void boo()
                 */
                trait Baz {}
                {
                }
                class Bar { use Baz; function zed() {} }
                $new = new Bar();
                $new->boo();
                EOT
            , 0
        ];
        yield 'methods from generic' => [
            <<<'EOT'
                <?php
                /**
                 * @template T
                 */
                class Baz {}
                /**
                 * @return Baz<Foo>
                 */
                function foo(){}
                $new = foo();
                $new->boo();
                EOT
            , 1
        ];
    }
}
