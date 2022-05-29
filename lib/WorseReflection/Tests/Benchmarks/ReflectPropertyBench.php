<?php

namespace Phpactor\WorseReflection\Tests\Benchmarks;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Tests\Benchmarks\Examples\PropertyClass;

/**
 * @Iterations(10)
 * @Revs(30)
 * @OutputTimeUnit("milliseconds", precision=2)
 * @Assert("mode(variant.time.avg) <= mode(baseline.time.avg) +/- 10%")
 */
class ReflectPropertyBench extends BaseBenchCase
{
    private ReflectionClassLike $class;

    public function setUp(): void
    {
        parent::setUp();
        $this->class = $this->getReflector()->reflectClassLike(ClassName::fromString(PropertyClass::class));
    }

    /**
     * @Subject()
     */
    public function property(): void
    {
        $this->class->properties()->get('noType');
    }

    /**
     * @Subject()
     */
    public function property_return_type(): void
    {
        $this->class->properties()->get('withType')->inferredType();
    }
}
