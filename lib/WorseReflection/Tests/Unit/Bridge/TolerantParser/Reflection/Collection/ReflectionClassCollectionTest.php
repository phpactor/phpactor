<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\TolerantParser\Reflection\Collection;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassLikeCollection;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Prophecy\PhpUnit\ProphecyTrait;

class ReflectionClassCollectionTest extends TestCase
{
    use ProphecyTrait;

    private $serviceLocator;

    private $reflection1;

    private $reflection2;

    private $reflection3;

    protected function setUp(): void
    {
        $this->serviceLocator = $this->prophesize(ServiceLocator::class);
        $this->reflection1 = $this->prophesize(ReflectionClass::class);
        $this->reflection2 = $this->prophesize(ReflectionClass::class);
        $this->reflection3 = $this->prophesize(ReflectionClass::class);
    }

    #[TestDox('It returns only concrete classes.')]
    public function testConcrete(): void
    {
        $this->reflection1->isConcrete()->willReturn(false);
        $this->reflection2->isConcrete()->willReturn(true);
        $this->reflection3->isConcrete()->willReturn(false);

        $collection = ReflectionClassLikeCollection::fromReflections([
            $this->reflection1->reveal(), $this->reflection2->reveal(), $this->reflection3->reveal()
        ]);

        $this->assertCount(1, $collection->concrete());
    }
}
