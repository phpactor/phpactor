<?php

namespace Phpactor\WorseReflection\Tests\Benchmarks;

use Phpactor\TextDocument\TextDocumentBuilder;

class AnalyserBench extends BaseBenchCase
{
    public function benchAnalyse(): void
    {
        $this->getReflector()->reflectOffset(TextDocumentBuilder::fromUri(__DIR__ . '/../../../../vendor/phpactor/tolerant-php-parser/src/Parser.php')->build(), 183744);
    }
}
