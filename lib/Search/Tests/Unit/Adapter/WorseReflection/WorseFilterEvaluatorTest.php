<?php

namespace Phpactor\Search\Tests\Unit\Adapter\WorseReflection;

use Generator;
use Microsoft\PhpParser\Parser;
use Phpactor\Search\Adapter\WorseReflection\TypedMatchToken;
use Phpactor\Search\Adapter\WorseReflection\TypedMatchTokens;
use Phpactor\Search\Adapter\WorseReflection\WorseFilterEvaluator;
use Phpactor\Search\Model\MatchToken;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\StringType;

class WorseFilterEvaluatorTest extends TestCase
{
    /**
     * @dataProvider provideEvaluate
     * @param array<string,TypedMatchToken> $vars
     */
    public function testEvaluate(string $expression, array $vars, Type $expected): void
    {
        $node = (new Parser())->parseSourceFile('<?php ' . $expression);
        $evaluated = (new WorseFilterEvaluator())->evaluate($node, new TypedMatchTokens($vars));
        self::assertEquals($expected, $evaluated);
    }

    /**
     * @return Generator<array{string,array<string,TypedMatchToken>,Type}>
     */
    public function provideEvaluate(): Generator
    {
        yield [
            'true === true',
            [],
            TypeFactory::boolLiteral(true),
        ];
        yield [
            'true === true',
            [],
            TypeFactory::boolLiteral(true),
        ];
        yield [
            '$A',
            [
                'A' => $this->matchToken('Foobar', TypeFactory::string()),
            ],
            TypeFactory::stringLiteral('Foobar'),
        ];
        yield [
            '$A === "Foobar"',
            [
                'A' => $this->matchToken('Foobar', TypeFactory::string()),
            ],
            TypeFactory::boolLiteral(true),
        ];
        yield [
            '$A instanceof "Foobar"',
            [
                'A' => $this->matchToken('Foobar', TypeFactory::string()),
            ],
            TypeFactory::boolLiteral(false),
        ];
        yield [
            '$A instanceof Foobar',
            [
                'A' => $this->matchToken('Foobar', TypeFactory::class('Foobar')),
            ],
            TypeFactory::boolLiteral(true),
        ];
    }

    private function matchToken(string $text, Type $type): TypedMatchToken
    {
        return new TypedMatchToken('', new MatchToken(ByteOffsetRange::fromInts(0, 0), $text, 0), $type);
    }
}
