<?php

namespace Phpactor\CodeTransform\Tests\Benchmark\Adapter\WorseReflection\Util;

use Generator;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeTransform\Adapter\WorseReflection\Helper\WorseUnresolvableClassNameFinder;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\ReflectorBuilder;

final class WorseUnresolvableClassNameFinderBench
{
    private WorseUnresolvableClassNameFinder $finder;

    public function __construct()
    {
        $locator = new class implements SourceCodeLocator {
            public function locate(Name $name): SourceCode
            {
                usleep(10);
                throw new SourceNotFound('Nope');
            }
        };
        $this->finder = new WorseUnresolvableClassNameFinder(
            ReflectorBuilder::create()
                ->enableCache()
                ->addLocator($locator)->build(),
            new Parser()
        );
    }

    /**
     * @ParamProviders("provideFind")
     */
    public function benchFind(array $params): void
    {
        $this->finder->find(TextDocumentBuilder::create($params['text'])->build());
    }

    public function provideFind(): Generator
    {
        foreach (['class', 'func'] as $type) {
            foreach (glob(__DIR__ . '/' . $type . '/*') as $path) {
                yield basename(dirname($path)) .'/' . basename($path) => [
                    'text' => file_get_contents($path)
                ];
            }
        }
    }
}
