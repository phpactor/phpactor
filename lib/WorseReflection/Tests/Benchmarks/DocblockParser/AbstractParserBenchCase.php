<?php

namespace Phpactor\WorseReflection\Tests\Benchmarks\DocblockParser;

/**
 * @Iterations(33)
 * @Revs(50)
 * @BeforeMethods({"setUp"})
 * @OutputTimeUnit("milliseconds")
 */
abstract class AbstractParserBenchCase
{
    abstract public function setUp(): void;

    public function benchParse(): void
    {
        $doc = <<<'EOT'
            /**
             * @param Foobar $foobar
             * @var Foobar $bafoo
             * @param string $baz
             */
            EOT;
        $this->parse($doc);
    }

    /**
     * @Revs(5)
     * @Iterations(10)
     */
    public function benchAssert(): void
    {
        $this->parse(file_get_contents(__DIR__ . '/examples/assert.example'));
    }

    abstract public function parse(string $doc): void;
}
