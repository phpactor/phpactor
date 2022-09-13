<?php

namespace Phpactor\Search\Tests\Unit\Model\Matcher;

use Closure;
use Generator;
use Microsoft\PhpParser\TokenKind;
use PHPUnit\Framework\TestCase;
use Phpactor\Search\Model\Matcher\PlaceholderMatcher;
use Phpactor\Search\Model\MatchResult;
use Phpactor\Search\Model\MatchToken;
use Phpactor\TextDocument\ByteOffsetRange;

class PlaceholderMatcherTest extends TestCase
{
    /**
     * @dataProvider provideMatches
     */
    public function testMatches(MatchToken $token1, MatchToken $token2, Closure $closure): void
    {
        $closure((new PlaceholderMatcher())->matches($token1, $token2));
    }

    /**
     * @return Generator<array{MatchToken,MatchToken,Closure(MatchResult): void}>
     */
    public function provideMatches(): Generator
    {
        yield [
            new MatchToken(ByteOffsetRange::fromInts(1, 2), 'Foobar', 1),
            new MatchToken(ByteOffsetRange::fromInts(1, 2), 'Barfoo', 1),
            function (MatchResult $result): void {
                self::assertTrue($result->isMaybe());
            }
        ];

        yield 'name' => [
            new MatchToken(ByteOffsetRange::fromInts(1, 2), 'Foobar', 1),
            new MatchToken(ByteOffsetRange::fromInts(1, 2), '__A__', 1),
            function (MatchResult $result): void {
                self::assertTrue($result->isYes());
            }
        ];
    }
}
