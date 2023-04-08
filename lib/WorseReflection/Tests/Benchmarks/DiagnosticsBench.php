<?php

namespace Phpactor\WorseReflection\Tests\Benchmarks;

use Generator;
use GlobIterator;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingMethodProvider;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\ReflectorBuilder;
use SplFileInfo;
use function Amp\Promise\wait;

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
            ->addDiagnosticProvider(new MissingMethodProvider())
            ->build();
    }

    /**
     * @BeforeMethods({"init"})
     * @ParamProviders({"providePaths"})
     * @param array{path:string} $params
     */
    public function benchDiagnostics(array $params): void
    {
        $diagnostics = wait($this->reflector->diagnostics(
            TextDocumentBuilder::fromUri($params['path'])->build()
        ));
    }

    /**
     * @return Generator<array{path:string}>
     */
    public function providePaths(): Generator
    {
        foreach ((new GlobIterator(__DIR__ . '/fixtures/diagnostics/*.test')) as $info) {
            assert($info instanceof SplFileInfo);
            yield $info->getFilename() => [
                'path' => $info->getRealPath()
            ];
        }
    }
}
