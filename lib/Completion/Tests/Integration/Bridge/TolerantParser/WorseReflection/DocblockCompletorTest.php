<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\WorseReflection;

use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Completion\Bridge\TolerantParser\TypeSuggestionProvider;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\DocblockCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Name\FullyQualifiedName;
use Phpactor\ReferenceFinder\Search\NameSearchResult;
use Phpactor\ReferenceFinder\Search\PredefinedNameSearcher;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;

class DocblockCompletorTest extends TestCase
{
    use ArraySubsetAsserts;

    /**
     * @param array<string> $expected
     */
    #[DataProvider('provideComplete')]
    public function testComplete(string $source, array $expected): void
    {
        $results = [
            NameSearchResult::create(
                'class',
                FullyQualifiedName::fromString('Namespace\Aardvark')
            ),
        ];

        [$source, $offset] = ExtractOffset::fromSource($source);
        $node = (new TolerantAstProvider())->parseString($source)->getDescendantNodeAtPosition((int)$offset);
        $suggestions = iterator_to_array((new DocblockCompletor(
            new TypeSuggestionProvider(new PredefinedNameSearcher($results)),
            new TolerantAstProvider(),
        ))->complete($node, TextDocumentBuilder::create($source)->build(), ByteOffset::fromInt((int)$offset)), false);
        $actualNames = array_map(fn (Suggestion $s) => $s->name(), $suggestions);
        foreach ($expected as $expectedName) {
            if (!in_array($expectedName, $actualNames)) {
                self::fail(sprintf(
                    'Expected "%s" to be in set of completion results: "%s"',
                    $expectedName,
                    implode('", "', $actualNames)
                ));
            }
        }
        $this->addToAssertionCount(1);
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideComplete(): Generator
    {
        yield 'not in docblock' => [
            '@param<>',
            []
        ];

        yield 'in docblock' => [
            '/** @para<> */',
            [
                '@param',
            ]
        ];

        yield 'in second-line docblock' => [
            '* @para<> */',
            [
                '@param',
            ]
        ];

        yield 'in second-line docblock with more spaces' => [
            '   *    @para<> */',
            [
                '@param',
            ]
        ];

        yield 'bare ampersand' => [
            '   *    @<>',
            DocblockCompletor::SUPPORTED_TAGS,
        ];

        yield 'param type' => [
            '   *    @param A<> */',
            [
                'Aardvark',
            ],
        ];

        yield 'param type no match' => [
            '   *    @param Zed<> */',
            [
            ],
        ];

        yield 'var type match' => [
            '   *    @var Aar<> */',
            [
                'Aardvark',
            ],
        ];

        yield 'throws type match' => [
            '   *    @throws Aar<> */',
            [
                'Aardvark',
            ],
        ];

        yield 'param variable' => [
            '<?php /*    @param Foobar $a<> */function bar($aardvark, $foo)',
            [
                '$aardvark',
            ],
        ];

        yield 'param variable for method' => [
            <<<'EOT'
                <?php
                class Bar
                {
                    /**
                     * @param string $s<>
                     */
                    private function resolveSingleType(string $search): string
                }
                EOT
            , [
                '$search',
            ],
        ];

        yield 'no var if not param' => [
            '<?php /*    @var Foobar $a<> */function bar($aardvark, $foo)',
            [
            ],
        ];

        yield 'property docblock for class' => [
            <<<'EOT'
                <?php

                use Foobar;

                /**
                 * @property A<>
                 */
                class Bar
                {
                    private function resolveSingleType(string $search): string
                }
                EOT
            , [
                'Foobar',
            ],
        ];
    }
}
