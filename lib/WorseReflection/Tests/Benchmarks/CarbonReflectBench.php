<?php

namespace Phpactor\WorseReflection\Tests\Benchmarks;

use Phpactor\TextDocument\TextDocumentBuilder;

/**
 * @Iterations(5)
 * @Revs(1)
 */
class CarbonReflectBench extends BaseBenchCase
{
    public function benchCarbonReflection(): void
    {
        $classes = $this->getReflector()->reflectClassesIn(TextDocumentBuilder::fromUri(__DIR__ . '/fixtures/reflection/carbon.test')->build());
        $carbon = $classes->get('Carbon\Carbon');
        foreach ($carbon->methods() as $method) {
        }
    }
}
