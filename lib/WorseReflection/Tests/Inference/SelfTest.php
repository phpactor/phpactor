<?php

namespace Phpactor\WorseReflection\Tests\Inference;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;

class SelfTest extends IntegrationTestCase
{
    public const DISABLED_TESTS = [
        // disabling the includeWalker because it barely works
        // and it causes severe performance issues.
        'require_and_include',
    ];

    #[DataProvider('provideSelf')]
    public function testSelf(string $path): void
    {
        if (self::shouldSkip($path)) {
            self::markTestSkipped(sprintf(
                'Feature not supported by current %s runtime',
                phpversion()
            ));
            ;
        }

        $source = TextDocumentBuilder::fromUri($path)->build();
        $reflector = $this->createBuilder($source)->enableCache()->build();
        $reflector->reflectOffset($source, mb_strlen($source));

        // the wrAssertType function in the source code will cause
        // an exception to be thrown if it fails
        $this->addToAssertionCount(1);
    }

    /**
     * @return Generator<mixed>mixed
     */
    public static function provideSelf(): Generator
    {
        foreach ((array)glob(__DIR__ . '/*/*.test') as $fname) {
            $dirName = basename(dirname((string)$fname));
            if (in_array($dirName, self::DISABLED_TESTS)) {
                continue;
            }
            yield $dirName .' ' . basename((string)$fname) => [
                $fname
            ];
        }
    }

    private static function shouldSkip(string $path): bool
    {
        if (!preg_match('{php-([0-9]+\.[0-9]+\.[0-9]+)-}', $path, $matches)) {
            return false;
        }

        return version_compare(phpversion(), $matches[1], 'lt');
    }
}
