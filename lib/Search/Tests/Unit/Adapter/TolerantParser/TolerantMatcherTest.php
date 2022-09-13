<?php

namespace Phpactor\Search\Tests\Unit\Adapter\TolerantParser;

use Closure;
use Generator;
use GlobIterator;
use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Phpactor\Search\Adapter\TolerantParser\TolerantMatcher;
use Phpactor\Search\Model\Matches;
use Phpactor\TextDocument\TextDocumentBuilder;
use SplFileInfo;

class TolerantMatcherTest extends TestCase
{
    /**
     * @dataProvider provideMatch
     */
    public function testMatch(string $document, string $pattern, Closure $assertion): void
    {
        $matches = (new TolerantMatcher(new Parser()))->match(TextDocumentBuilder::create($document)->build(), $pattern);
        $assertion($matches);
    }

    /**
     * @return Generator<array{string,string,Closure(Matches): void}>
     */
    public function provideMatch(): Generator
    {
        $cases = iterator_to_array($this->cases());
        /** @var SplFileInfo $splFileInfo */
        foreach ((new GlobIterator(__DIR__ . '/source/*.test')) as $splFileInfo) {
            $caseName = $splFileInfo->getBasename();

            foreach ($cases as $case) {
                if ($case[0] !== $caseName) {
                    continue;
                }
                yield [
                    (string)file_get_contents($splFileInfo->getPathname()),
                    $case[1],
                    $case[2]
                ];
            }
        }
    }

    /**
     * @return Generator<array-key,array{string,string,Closure(Matches): void}>
     */
    public function cases(): Generator
    {
        yield [
            'class_only.test',
            'class Foo {}',
            function (Matches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
        yield [
            'class_only.test',
            'class Bar {}',
            function (Matches $matches): void {
                self::assertCount(0, $matches);
            }
        ];
        yield [
            'class_only.test',
            'class Foo extends {}',
            function (Matches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
        yield [
            'class_only.test',
            'class Foo extends Bazfoo {}',
            function (Matches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
        yield [
            'class_only.test',
            'class Foo implements Gar {}',
            function (Matches $matches): void {
                self::assertCount(0, $matches);
            }
        ];
        yield [
            'class_with_method.test',
            'class Foo { function bar() {} }',
            function (Matches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
        yield [
            'class_with_method.test',
            'class Foo { function baz() {} }',
            function (Matches $matches): void {
                self::assertCount(0, $matches);
            }
        ];
        yield [
            'class_with_method.test',
            'class Foo { abstract function bar() {} }',
            function (Matches $matches): void {
                self::assertCount(0, $matches);
            }
        ];
        yield [
            'class_with_method.test',
            'class Foo { public function bar() {} }',
            function (Matches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
        yield [
            'class_with_method.test',
            'class Foo { private function bar() {} }',
            function (Matches $matches): void {
                self::assertCount(0, $matches);
            }
        ];
        yield [
            'class_with_private_method.test',
            'class Foo { private function bar() {} }',
            function (Matches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
        yield [
            'assign_string_literal.test',
            "'hello'",
            function (Matches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
        yield [
            'assign_string_literal.test',
            "\$foo = 'hello'",
            function (Matches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
        yield [
            'assign_string_literal.test',
            "\$bar = 'hello'",
            function (Matches $matches): void {
                self::assertCount(0, $matches);
            }
        ];
        yield [
            'assign_string_literal.test',
            "\$foo",
            function (Matches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
        yield [
            'class_with_sprintf.test',
            "sprintf()",
            function (Matches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
    }
}
