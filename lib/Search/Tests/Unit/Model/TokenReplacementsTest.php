<?php

namespace Phpactor\Search\Tests\Unit\Model;

use Closure;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Search\Adapter\TolerantParser\TolerantMatchFinder;
use Phpactor\Search\Model\TokenReplacement;
use Phpactor\Search\Model\TokenReplacements;
use Phpactor\TextDocument\TextDocumentBuilder;

class TokenReplacementsTest extends TestCase
{
    /**
     * @dataProvider provideReplace
     * @param TokenReplacement[] $replacements
     */
    public function testReplace(string $document, string $template, array $replacements, Closure $assertion): void
    {
        $matches = TolerantMatchFinder::createDefault()->match(TextDocumentBuilder::create($document)->build(), $template);
        $replacements = new TokenReplacements(...$replacements);
        $replaced = $replacements->applyTo($matches);
        $assertion($replaced);
    }

    /**
     * @return Generator<array{string,string,array<int,TokenReplacement>,Closure(string): void}>
     */
    public function provideReplace(): Generator
    {
        yield [
            '<?php class Foobar {}',
            'class __A__ {}',
            [
                new TokenReplacement('A', 'Barfoo'),
            ],
            function (string $document): void {
                self::assertStringContainsString('Barfoo', $document);
                self::assertStringNotContainsString('Foobar', $document);
            }
        ];
        yield [
            '<?php class Foobar {} class Foobar2{}',
            'class __A__ {}',
            [
                new TokenReplacement('A', 'Barfoo'),
            ],
            function (string $document): void {
                self::assertStringContainsString('Barfoo', $document);
                self::assertStringNotContainsString('Foobar', $document);
            }
        ];
        yield [
            '<?php class Foobar { function methodOne() {} } class Baz{ }',
            'class __A__ { function __C__() {}}',
            [
                new TokenReplacement('A', 'Barfoo'),
                new TokenReplacement('C', 'methodTwo'),
            ],
            function (string $document): void {
                self::assertStringContainsString('Barfoo', $document);
                self::assertStringNotContainsString('Foobar', $document);
                self::assertStringContainsString('methodTwo', $document);
                self::assertStringNotContainsString('methodOne', $document);

                // class baz did not match the template
                self::assertStringContainsString('Baz', $document);
            }
        ];
    }
}
