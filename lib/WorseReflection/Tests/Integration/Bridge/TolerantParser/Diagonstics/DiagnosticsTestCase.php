<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Diagonstics;

use Generator;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;

abstract class DiagnosticsTestCase extends IntegrationTestCase
{
    /**
     * @dataProvider provideDiagnostic
     */
    public function testDiagnostic(string $path): void
    {
        $source = (string)file_get_contents($path);
        $reflector = $this->createBuilder($source)->enableCache()->addDiagnosticProvider($this->provider())->build();
        $reflector->reflectOffset($source, mb_strlen($source));

        // the wrAssertType function in the source code will cause
        // an exception to be thrown if it fails
        $this->addToAssertionCount(1);
    }

    /**
     * @return Generator<mixed>mixed
     */
    public function provideDiagnostic(): Generator
    {
        $shortName = substr(static::class, strrpos(__CLASS__, '\\') + 1, -4);
        foreach ((array)glob(__DIR__ . '/' . $shortName . '/*.test') as $fname) {
            yield $shortName .' ' . basename((string)$fname) => [
                $fname
            ];
        }
    }

    abstract protected function provider(): DiagnosticProvider;
}
