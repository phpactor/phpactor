<?php

namespace Phpactor\WorseReflection\Tests\SelfTest;

use Generator;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;

class SelfTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideSelf
     */
    public function testSelf(string $path): void
    {
        $source = (string)file_get_contents($path);
        $reflector = $this->createReflector($source);
        $reflector->reflectOffset($source, mb_strlen($source));

        // the wrAssertType function in the source code will cause
        // an exception to be thrown if it fails
        $this->addToAssertionCount(1);
    }

    /**
     * @return Generator<mixed>mixed
     */
    public function provideSelf(): Generator
    {
        foreach ([
            'assignment',
            'flow',
            'generics',
        ] as $topic) {
            foreach ((array)glob(__DIR__ . '/' . $topic . '/*.test') as $fname) {
                yield $topic .' ' . basename((string)$fname) => [
                    $fname
                ];
            }
        }
    }
}
