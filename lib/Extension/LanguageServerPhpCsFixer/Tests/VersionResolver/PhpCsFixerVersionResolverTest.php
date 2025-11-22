<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\Tests\VersionResolver;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerPhpCsFixer\VersionResolver\PhpCsFixerVersionResolver;
use Psr\Log\NullLogger;

use function Amp\call;
use function Amp\Promise\wait;

class PhpCsFixerVersionResolverTest extends TestCase
{
    public function testResolve(): void
    {
        $path = realpath(__DIR__ . '/../../../../../vendor/bin/php-cs-fixer');

        self::assertNotFalse($path);

        $resolver = new PhpCsFixerVersionResolver($path, new NullLogger());

        $process = call(function () use ($resolver) {
            $version = yield $resolver->resolve();

            self::assertMatchesRegularExpression('/^\d+\.\d+\.\d+.*$/', $version->__toString());
        });

        wait($process);
    }
}
