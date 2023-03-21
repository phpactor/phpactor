<?php

namespace Phpactor\WorseReflection\Tests\Inference;

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
        $reflector = $this->createBuilder($source)->enableCache()->build();
        $reflected = $reflector->reflectOffset($source, mb_strlen($source));
        dump($reflected->nodeContext()->__toString());

        // the wrAssertType function in the source code will cause
        // an exception to be thrown if it fails
        $this->addToAssertionCount(1);
    }

    /**
     * @return Generator<mixed>mixed
     */
    public function provideSelf(): Generator
    {
        foreach ((array)glob(__DIR__ . '/*/*.test') as $fname) {
            $dirName = basename(dirname((string)$fname));
            if ($dirName === 'enum' && !defined('T_ENUM')) {
                continue;
            }
            yield $dirName .' ' . basename((string)$fname) => [
                $fname
            ];
        }
    }
}
