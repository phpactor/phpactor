<?php

namespace Phpactor\WorseReflection\Tests\Benchmarks;

use Phpactor\WorseReflection\Core\ClassName;

/**
 * @Iterations(5)
 * @Revs(10)
 * @OutputTimeUnit("milliseconds", precision=2)
 * @Assert("mode(variant.time.avg) <= mode(baseline.time.avg) +/- 10%")
 * @Assert("mode(variant.mem.peak) <= mode(baseline.mem.peak) +/- 10%")
 */
class SelfReflectClassBench extends BaseBenchCase
{
    public function benchMethodsAndProperties(): void
    {
        $class = $this->getReflector()->reflectClassLike(ClassName::fromString(self::class));

        foreach ($class->methods() as $method) {
            foreach ($method->parameters() as $parameter) {
                $method->inferredType();
            }
        }
    }

    public function benchFrames(): void
    {
        $class = $this->getReflector()->reflectClassLike(ClassName::fromString(self::class));

        foreach ($class->methods() as $method) {
            $method->frame();
        }
    }
}
