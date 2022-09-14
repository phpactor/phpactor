<?php

namespace Phpactor\Search\Tests\Unit\Adapter\TolerantParser;

use Closure;
use Generator;
use GlobIterator;
use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Phpactor\Search\Adapter\TolerantParser\Matcher\TokenEqualityMatcher;
use Phpactor\Search\Model\Matcher\PlaceholderMatcher;
use Phpactor\Search\Adapter\TolerantParser\TolerantMatchFinder;
use Phpactor\Search\Model\Matcher\ChainMatcher;
use Phpactor\Search\Model\DocumentMatches;
use Phpactor\TextDocument\TextDocumentBuilder;
use SplFileInfo;

class TolerantMatchFinderTest extends TestCase
{
    /**
     * @dataProvider provideMatch
     */
    public function testMatch(string $document, string $pattern, Closure $assertion): void
    {
        $matches = (
            new TolerantMatchFinder(
                new Parser(),
                new ChainMatcher(
                    new PlaceholderMatcher(),
                    new TokenEqualityMatcher(),
                )
            )
        )->match(TextDocumentBuilder::create($document)->build(), $pattern);
        $assertion($matches);
    }

    /**
     * @return Generator<array{string,string,Closure(DocumentMatches): void}>
     */
    public function provideMatch(): Generator
    {
        $cases = array_merge(
            iterator_to_array($this->cases()),
            iterator_to_array($this->placeholderCases())
        );
        /** @var SplFileInfo $splFileInfo */
        foreach ((new GlobIterator(__DIR__ . '/source/*.test')) as $splFileInfo) {
            $caseName = $splFileInfo->getBasename();

            foreach ($cases as $name => $case) {
                if ($case[0] !== $caseName) {
                    continue;
                }
                yield $name => [
                    (string)file_get_contents($splFileInfo->getPathname()),
                    $case[1],
                    $case[2]
                ];
            }
        }
    }

    /**
     * @return Generator<array-key,array{string,string,Closure(DocumentMatches): void}>
     */
    public function cases(): Generator
    {
        yield [
            'class_only.test',
            'class Foo {}',
            function (DocumentMatches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
        yield [
            'class_only.test',
            'class Bar {}',
            function (DocumentMatches $matches): void {
                self::assertCount(0, $matches);
            }
        ];
        yield [
            'class_only.test',
            'class Foo extends {}',
            function (DocumentMatches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
        yield [
            'class_only.test',
            'class Foo extends Bazfoo {}',
            function (DocumentMatches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
        yield [
            'class_only.test',
            'class Foo implements Gar {}',
            function (DocumentMatches $matches): void {
                self::assertCount(0, $matches);
            }
        ];
        yield [
            'class_with_method.test',
            'class Foo { function bar() {} }',
            function (DocumentMatches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
        yield [
            'class_with_method.test',
            'class Foo { function baz() {} }',
            function (DocumentMatches $matches): void {
                self::assertCount(0, $matches);
            }
        ];
        yield [
            'class_with_method.test',
            'class Foo { abstract function bar() {} }',
            function (DocumentMatches $matches): void {
                self::assertCount(0, $matches);
            }
        ];
        yield [
            'class_with_method.test',
            'class Foo { public function bar() {} }',
            function (DocumentMatches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
        yield [
            'class_with_method.test',
            'class Foo { private function bar() {} }',
            function (DocumentMatches $matches): void {
                self::assertCount(0, $matches);
            }
        ];
        yield 'static method' => [
            'class_with_static_method.test',
            'class Foo { public static function bar() {} }',
            function (DocumentMatches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
        yield [
            'class_with_private_method.test',
            'class Foo { private function bar() {} }',
            function (DocumentMatches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
        yield [
            'assign_string_literal.test',
            "'hello'",
            function (DocumentMatches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
        yield [
            'assign_string_literal.test',
            "\$foo = 'hello'",
            function (DocumentMatches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
        yield [
            'assign_string_literal.test',
            "\$bar = 'hello'",
            function (DocumentMatches $matches): void {
                self::assertCount(0, $matches);
            }
        ];
        yield [
            'assign_string_literal.test',
            '$foo',
            function (DocumentMatches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
        yield [
            'class_with_sprintf.test',
            'sprintf()',
            function (DocumentMatches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
        yield 'string does not match heredoc' => [
            'example_with_heredocs.test',
            "'hello'",
            function (DocumentMatches $matches): void {
                self::assertCount(0, $matches);
            }
        ];
        yield 'member access' => [
            'this_member_access.test',
            '$this->assertEquals()',
            function (DocumentMatches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
    }

    /**
     * @return Generator<array-key,array{string,string,Closure(DocumentMatches): void}>
     */
    public function placeholderCases(): Generator
    {
        yield [
            'assign_string_literal.test',
            '$__a__',
            function (DocumentMatches $matches): void {
                self::assertCount(1, $matches);
                self::assertEquals('$foo', $matches->first()->tokens()->get('a')->text);
            }
        ];
        yield 'placeholder' => [
            'class_placeholder_with_method.test',
            'class __A__ {}',
            function (DocumentMatches $matches): void {
                self::assertCount(1, $matches);
                self::assertEquals('ThisShouldBeCaptured', $matches->first()->tokens()->get('A')->text);
            }
        ];
        yield [
            'class_placeholder_with_method.test',
            'class __A__ { public function baz() {}}',
            function (DocumentMatches $matches): void {
                self::assertCount(0, $matches);
            }
        ];
        yield [
            'class_placeholder_with_method.test',
            'class __A__ { function bar() {}}',
            function (DocumentMatches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
        yield [
            'class_placeholder_with_no_methods.test',
            'class __A__ { function bar() {}}',
            function (DocumentMatches $matches): void {
                self::assertCount(0, $matches);
            }
        ];
        yield 'returns match from multiple' => [
            'class_placeholder_with_multiple_method.test',
            'class __A__ { function bar() {}}',
            function (DocumentMatches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
    }
}
