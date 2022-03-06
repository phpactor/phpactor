<?php

namespace Phpactor\Indexer\Tests\Adapter\Tolerant\Indexer;

use Closure;
use Generator;
use Phpactor\Indexer\Adapter\Tolerant\Indexer\ConstantDeclarationIndexer;
use Phpactor\Indexer\IndexAgent;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record\ConstantRecord;
use Phpactor\Indexer\Tests\Adapter\Tolerant\TolerantIndexerTestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class ConstantDeclarationIndexerTest extends TolerantIndexerTestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider provideDeclaration
     */
    public function testDeclaration(string $manifest, Closure $assertion): void
    {
        $this->workspace()->reset();
        $this->workspace()->loadManifest($manifest);

        $agent = $this->indexAgentBuilder('src')
            ->setIndexers([
                new ConstantDeclarationIndexer()
            ])->buildAgent();

        $agent->indexer()->getJob()->run();

        $assertion($agent);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideDeclaration(): Generator
    {
        yield 'no implementations' => [
            "// File: src/file1.php\n<?php const BARFOO = 1",
            function (IndexAgent $agent): void {
                self::assertNull(
                    $agent->query()->constant()->get('FOOBAR')
                );
            }
        ];
        yield 'const 1' => [
            "// File: src/file1.php\n<?php const FOOBAR = 1",
            function (IndexAgent $agent): void {
                self::assertInstanceOf(
                    ConstantRecord::class,
                    $agent->query()->constant()->get('FOOBAR')
                );

                self::assertCount(1, iterator_to_array(
                    $agent->search()->search(
                        Criteria::and(
                            Criteria::isConstant(),
                            Criteria::fqnBeginsWith('FOOBAR')
                        )
                    )
                ));
            }
        ];
        yield 'declare 1' => [
            "// File: src/file1.php\n<?php define('FOOBAR', 1)",
            function (IndexAgent $agent): void {
                self::assertInstanceOf(
                    ConstantRecord::class,
                    $agent->query()->constant()->get('FOOBAR')
                );

                self::assertCount(1, iterator_to_array(
                    $agent->search()->search(
                        Criteria::and(
                            Criteria::isConstant(),
                            Criteria::fqnBeginsWith('FOOBAR')
                        )
                    )
                ));
            }
        ];
    }
}
