<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection\Collection;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Closure;

class ReflectionParameterCollectionTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideCollection
     */
    public function testCollection(string $source, Closure $assertion): void
    {
        $collection = $this->createReflector($source)->reflectClassesIn($source)->first()->methods()->first()->parameters();
        $assertion($collection);
    }

    public function provideCollection()
    {
        return [
            'returns promoted parameters' => [
                <<<'EOT'
                    <?php

                    class Foobar
                    {
                        public function bar(private $foo, string $cat, private $bar) {}
                    }
                    EOT
                ,
                function (ReflectionParameterCollection $collection): void {
                    $this->assertEquals(3, $collection->count());
                    $this->assertEquals(1, $collection->notPromoted()->count());
                    $this->assertEquals(2, $collection->promoted()->count());
                },
            ],
        ];
    }
}
