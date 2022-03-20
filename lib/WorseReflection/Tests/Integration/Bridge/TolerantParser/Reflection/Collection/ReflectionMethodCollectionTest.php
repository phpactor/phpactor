<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection\Collection;

use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Closure;

class ReflectionMethodCollectionTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideCollection
     */
    public function testCollection(string $source, Closure $assertion): void
    {
        $collection = $this->createReflector($source)->reflectClass('Foobar');
        $assertion($collection);
    }

    public function provideCollection()
    {
        yield 'Get abstract methods' => [
            <<<'EOT'
                <?php

                abstract class Foobar
                {
                    public function one() {}

                    abstract function two() {}
                    abstract function three() {}
                }

                EOT
        ,
            function (ReflectionClass $class): void {
                $this->assertEquals(2, $class->methods()->abstract()->count());
            },
        ];

        yield 'Get abstract methods with virtual methods' => [
            <<<'EOT'
                <?php

                /**
                * @method Barfoo barfoo()
                 */
                abstract class Foobar
                {
                    public function one() {}

                    abstract function two() {}
                    abstract function three() {}
                }

                EOT
        ,
            function (ReflectionClass $class): void {
                $this->assertEquals(2, $class->methods()->abstract()->count());
            },
        ];
    }
}
