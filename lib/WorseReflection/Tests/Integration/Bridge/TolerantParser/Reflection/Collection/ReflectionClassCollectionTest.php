<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection\Collection;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassLikeCollection;
use Closure;

class ReflectionClassCollectionTest extends IntegrationTestCase
{
    #[DataProvider('provideCollection')]
    public function testCollection(string $source, Closure $assertion): void
    {
        $collection = $this->createReflector($source)->reflectClassesIn(TextDocumentBuilder::create($source)->build());
        $assertion($collection);
    }

    /**
     * @return Generator<string, array{string, Closure(ReflectionClassLikeCollection):void}>
     */
    public function provideCollection(): Generator
    {
        yield 'It has all the classes' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                }

                class Barfoo
                {
                }
                EOT
            ,
            function (ReflectionClassLikeCollection $collection): void {
                $this->assertEquals(2, $collection->count());
            },
        ];
        yield 'It reflects nested classes' => [
            <<<'EOT'
                <?php

                if (true) {
                    class Foobar
                    {
                    }
                }
                EOT
            ,
            function (ReflectionClassLikeCollection $collection): void {
                $this->assertEquals(1, $collection->count());
            },
        ];
    }
}
