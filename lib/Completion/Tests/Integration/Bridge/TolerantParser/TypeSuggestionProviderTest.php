<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser;

use PHPUnit\Framework\Attributes\DataProvider;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Generator;
use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Phpactor\Completion\Bridge\TolerantParser\TypeSuggestionProvider;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Name\FullyQualifiedName;
use Phpactor\ReferenceFinder\Search\NameSearchResult;
use Phpactor\ReferenceFinder\Search\PredefinedNameSearcher;
use Phpactor\TestUtils\ExtractOffset;

class TypeSuggestionProviderTest extends TestCase
{
    use ArraySubsetAsserts;
    /**
     * @param array<string> $expected
     */
    #[DataProvider('provideProvide')]
    public function testProvide(string $source, string $search, array $expected): void
    {
        $searcher = new PredefinedNameSearcher([
            NameSearchResult::create(
                'class',
                FullyQualifiedName::fromString('Namespace\Aardvark')
            ),
        ]);
        [$source, $offset] = ExtractOffset::fromSource($source);
        $node = (new Parser())->parseSourceFile($source)->getDescendantNodeAtPosition((int)$offset);
        $suggestions = iterator_to_array((new TypeSuggestionProvider($searcher))->provide($node, $search));
        self::assertArraySubset(
            $expected,
            array_map(fn (Suggestion $s) => $s->name(), $suggestions)
        );
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideProvide(): Generator
    {
        yield [
            '<?php <>',
            '',
            [],
        ];
        yield 'imported name' => [
            '<?php use Foobar; new bar();<>',
            'F',
            [
                'Foobar',
            ],
        ];
        yield 'scalar' => [
            '',
            '',
            [
                'string',
                'float',
                'int',
            ],
        ];
        yield 'generic' => [
            '',
            'Foo<s',
            [
                'string',
            ],
        ];
        yield 'union' => [
            '',
            'Foo|',
            [
                'string',
            ],
        ];
        yield 'intersection' => [
            '',
            'Foo&',
            [
                'string',
            ],
        ];
    }
}
