<?php

namespace Phpactor\Extension\PHPUnit\CodeTransform;

use Phpactor\CodeTransform\Domain\ClassName;
use Phpactor\CodeTransform\Domain\GenerateNew;
use Phpactor\CodeTransform\Domain\SourceCode;

class TestGenerator implements GenerateNew
{
    public function generateNew(ClassName $targetName): SourceCode
    {
        $namespace = $targetName->namespace();
        $name = $targetName->short();
        $sourceCode = <<<EOT
            <?php

            namespace $namespace;

            use PHPUnit\Framework\TestCase;

            class $name extends TestCase
            {
            }
            EOT
        ;

        return SourceCode::fromString($sourceCode);
    }
}
