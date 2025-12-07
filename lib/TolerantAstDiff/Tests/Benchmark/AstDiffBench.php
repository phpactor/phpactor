<?php

namespace Phpactor\TolerantAstDiff\Tests\Benchmark;

use Phpactor\TolerantAstDiff\AstDiff;
use Phpactor\WorseReflection\Bridge\TolerantParser\Parser\MergingParser;

final class AstDiffBench
{
    public function benchMerge(): void
    {
        $largeFile = __DIR__ . '/../../AstDiff.php';
        $mergingParser = new MergingParser(new AstDiff());
        $contents = file_get_contents($largeFile);

        $ast1 = $mergingParser->parseSourceFile($contents, __FILE__);

        $contents = (string)substr($contents, 0, 150);
        $contents .= "\n";
        $contents .= "\n";

        $ast2 = $mergingParser->parseSourceFile($contents, __FILE__);
    }
}
