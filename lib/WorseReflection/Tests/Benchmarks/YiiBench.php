<?php

namespace Phpactor\WorseReflection\Tests\Benchmarks;

/**
 * @Iterations(5)
 * @Revs(1)
 */
class YiiBench extends BaseBenchCase
{
    public function install(): void
    {
        $this->loadFixture('yii');
    }

    /**
     * @BeforeMethods({"setUp", "install"})
     */
    public function benchMembers(): void
    {
        $reflection = $this->getReflector()->reflectClass('Phpactor\WorseReflection\Tests\Workspace\Record');
        foreach ($reflection->members() as $method) {
            $method->inferredType();
        }
    }
}
