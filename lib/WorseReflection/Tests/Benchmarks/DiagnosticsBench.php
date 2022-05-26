<?php

namespace Phpactor\WorseReflection\Tests\Benchmarks;

use Phpactor\WorseReflection\Bridge\Phpactor\MemberProvider\DocblockMemberProvider;
use Phpactor\WorseReflection\Bridge\Phpactor\MemberProvider\MixinMemberProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingMethods;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\ReflectorBuilder;


/**
 * @Iterations(5)
 * @Revs(1)
 */
class DiagnosticsBench
{
    private Reflector $reflector;

    public function init(): void
    {
        $this->reflector = ReflectorBuilder::create()
            ->addDiagnosticProvider(new MissingMethods())
            ->enableContextualSourceLocation()
            ->enableCache()
            ->build();
    }

    /**
     * @BeforeMethods({"init"})
     */
    public function benchDiagnostics(): void
    {
        $diagnostics = $this->reflector->diagnostics(
            file_get_contents(__DIR__ . '/fixtures/diagnostics/lots_of_missing_methods.test')
        );
    }
}
