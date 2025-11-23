<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser;

use Prophecy\PhpUnit\ProphecyTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\DoctrineAnnotationCompletor;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Tests\Integration\CompletorTestCase;
use Phpactor\ReferenceFinder\NameSearcher;
use Phpactor\ReferenceFinder\Search\NameSearchResult;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Bridge\Phpactor\MemberProvider\DocblockMemberProvider;
use Phpactor\WorseReflection\ReflectorBuilder;
use Prophecy\Argument;

class DoctrineAnnotationCompletorTest extends CompletorTestCase
{
    use ProphecyTrait;
    #[DataProvider('provideComplete')]
    public function testComplete(string $source, array $expected): void
    {
        $this->assertComplete($source, $expected);
    }

    public static function provideComplete(): Generator
    {
        yield 'not a docblock' => [
            <<<'EOT'
                <?php

                /**
                 * @Annotation
                 */
                class Annotation {}

                /*
                 * @Ann<>
                 */
                class Foo {}
                EOT
            , []
        ];

        yield 'not a text annotation' => [
            <<<'EOT'
                <?php

                /**
                 * Ann<>
                 */
                class Foo {}
                EOT
            , []
        ];

        yield 'in a namespace' => [
            <<<'EOT'
                <?php

                namespace App\Annotation {
                    /**
                     * @Annotation
                     */
                    class Entity {}
                }

                namespace App {
                    /**
                     * @Ent<>
                     */
                    class Foo {}
                }
                EOT
        , [
            [
                'type' => Suggestion::TYPE_CLASS,
                'name' => 'Entity',
                'short_description' => 'App\Annotation\Entity',
                'snippet' => 'Entity($1)$0'
            ]
        ]];

        yield 'annotation on a node in the middle of the AST' => [
            <<<'EOT'
                <?php

                /**
                 * @Annotation
                 */
                class Annotation {}

                class Foo
                {
                    /**
                     * @var string
                     */
                    private $foo;

                    /**
                     * @Ann<>
                     */
                    public function foo(): string
                    {
                        return 'foo';
                    }

                    public function bar(): string
                    {
                        return 'bar';
                    }
                }
                EOT
        , [
            [
                'type' => Suggestion::TYPE_CLASS,
                'name' => 'Annotation',
                'short_description' => 'Annotation',
                'snippet' => 'Annotation($1)$0'
            ]
        ]];

        yield 'not an annotation class' => [
            <<<'EOT'
                <?php

                class NotAnnotation {}

                /**
                 * @NotAnn<>
                 */
                class Foo {}
                EOT
            , []
        ];

        yield 'handle errors if class not found' => [
            <<<'EOT'
                <?php

                /**
                 * @NotAnn<>
                 */
                class Foo {}
                EOT
            , []
        ];
    }

    protected function createCompletor(string $source): Completor
    {
        $source = TextDocumentBuilder::create($source)->uri('file:///tmp/test')->build();

        $searcher = $this->prophesize(NameSearcher::class);
        $searcher->search(Argument::any())->willYield([]);
        $searcher->search('Ann', null)->willYield([
            NameSearchResult::create('class', 'Annotation')
        ]);
        $searcher->search('Ent', null)->willYield([
            NameSearchResult::create('class', 'App\Annotation\Entity')
        ]);
        $searcher->search('NotAnn', null)->willYield([
            NameSearchResult::create('class', 'NotAnnotation')
        ]);

        $reflector = ReflectorBuilder::create()
            ->addMemberProvider(new DocblockMemberProvider())
            ->addSource($source)->build();

        return new DoctrineAnnotationCompletor(
            $searcher->reveal(),
            $reflector,
        );
    }
}
