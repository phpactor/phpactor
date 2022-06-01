<?php

namespace Phpactor\WorseReflection\Tests\Benchmarks;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\ClassName;

/**
 * @Iterations(5)
 * @Revs(1)
 */
class PhpUnitReflectClassBench extends BaseBenchCase
{
    /**
     * @Subject()
     * @OutputTimeUnit("microseconds", precision=4)
     */
    public function test_case(): void
    {
        $this->getReflector()->reflectClassLike(ClassName::fromString(TestCase::class));
    }

    /**
     * @Subject()
     * @OutputTimeUnit("milliseconds", precision=2)
     * @Assert("mode(variant.time.avg) <= mode(baseline.time.avg) +/- 10%")
     */
    public function test_case_methods_and_properties(): void
    {
        $class = $this->getReflector()->reflectClassLike(ClassName::fromString(TestCase::class));

        foreach ($class->methods() as $method) {
            foreach ($method->parameters() as $parameter) {
                $method->type();
            }
        }
    }

    /**
     * @Subject()
     * @Revs(1)
     * @OutputTimeUnit("milliseconds", precision=2)
     * @Assert("mode(variant.time.avg) <= mode(baseline.time.avg) +/- 10%")
     */
    public function test_case_method_frames(): void
    {
        $class = $this->getReflector()->reflectClassLike(ClassName::fromString(TestCase::class));

        foreach ($class->methods() as $method) {
            $method->frame();
        }
    }
}
