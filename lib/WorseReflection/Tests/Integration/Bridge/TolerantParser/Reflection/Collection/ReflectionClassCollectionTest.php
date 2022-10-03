<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection\Collection;

use Closure;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassLikeCollection;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;

class ReflectionClassCollectionTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideCollection
     */
    public function testCollection(string $source, Closure $assertion): void
    {
        $collection = $this->createReflector($source)->reflectClassesIn($source);
        $assertion($collection);
    }

    public function provideCollection()
    {
        return [
            'It has all the classes' => [
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
            ],
            'It reflects nested classes' => [
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
            ],
        ];
    }
}
