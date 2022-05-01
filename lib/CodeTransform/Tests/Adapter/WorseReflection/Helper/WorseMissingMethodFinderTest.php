<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Helper;

use Generator;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeTransform\Adapter\WorseReflection\Helper\WorseMissingMethodFinder;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\TextDocumentBuilder;

class WorseMissingMethodFinderTest extends WorseTestCase
{
    /**
     * @dataProvider provideFindMissingMethods
     */
    public function testFindMissingMethods(string $source, int $expectedCount): void
    {
        [$source, $offset] = ExtractOffset::fromSource($source);
        $reflector = $this->reflectorForWorkspace($source);
        $document = TextDocumentBuilder::create($source)->build();

        $methods = (new WorseMissingMethodFinder($reflector, new Parser()))->find($document, $offset);
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
        yield 'missing method' => [
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
    }
}
