<?php

namespace Phpactor\Search\Tests\Unit\Adapter\WorseReflection;

use Closure;
use Generator;
use Microsoft\PhpParser\Parser;
use Phpactor\Search\Adapter\TolerantParser\Matcher\TokenEqualityMatcher;
use Phpactor\Search\Adapter\TolerantParser\TolerantMatchFinder;
use Phpactor\Search\Adapter\WorseReflection\WorseMatchFilter;
use Phpactor\Search\Model\Constraint\TextConstraint;
use Phpactor\Search\Model\Constraint\TypeConstraint;
use Phpactor\Search\Model\DocumentMatches;
use Phpactor\Search\Model\Matcher\ChainMatcher;
use Phpactor\Search\Model\Matcher\PlaceholderMatcher;
use Phpactor\Search\Model\PatternMatch;
use Phpactor\Search\Model\TokenConstraint;
use Phpactor\Search\Model\TokenConstraints;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\ReflectorBuilder;

class WorseMatchFilterTest extends TestCase
{

    /**
     * @dataProvider provideFilter
     * @param TokenConstraint[] $constraints
     */
    public function testFilter(string $document, string $template, array $constraints, Closure $assertion): void
    {
        $document = TextDocumentBuilder::create($document)->build();
        $matches = TolerantMatchFinder::createDefault()->match($document, $template);
        $matches = (new WorseMatchFilter(ReflectorBuilder::create()->build()))->filter(
            $matches,
            new TokenConstraints(...$constraints)
        );

        $assertion($matches);
    }

    /**
     * @return Generator<array{string,string,array<TokenConstraint>,Closure(DocumentMatches): void}>
     */
    public function provideFilter(): Generator
    {
        yield [
            '<?php class Foobar {}',
            'class __A__ {}',
            [
            ],
            function (DocumentMatches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
        yield [
            '<?php class Foobar {}',
            'class __A__ {}',
            [
                new TextConstraint('A', 'Foobar'),
            ],
            function (DocumentMatches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
        yield 'filters out when tokens are depleted by filter' => [
            '<?php class Foobar {}',
            'class __A__ {}',
            [
                new TextConstraint('A', 'Barfoo'),
            ],
            function (DocumentMatches $matches): void {
                self::assertCount(0, $matches);
            }
        ];
        yield 'filters out when any tokens are depleted by filter' => [
            '<?php class Foobar {function method() {}}',
            'class __A__ { function __B__(){}}',
            [
                new TextConstraint('A', 'Foobar'),
                new TextConstraint('B', 'Something'),
            ],
            function (DocumentMatches $matches): void {
                self::assertCount(0, $matches);
            }
        ];
        yield 'filters by type' => [
            '<?php class Foobar {function method() {}}',
            'class __A__ { function __B__(){}}',
            [
                new TypeConstraint('A', 'Foobar'),
            ],
            function (DocumentMatches $matches): void {
                self::assertCount(1, $matches);
            }
        ];
        yield 'does not filter if type wrong' => [
            '<?php class Foobar {function method() {}}',
            'class __A__ { function __B__(){}}',
            [
                new TypeConstraint('A', 'Barr'),
            ],
            function (DocumentMatches $matches): void {
                self::assertCount(0, $matches);
            }
        ];
    }
}
