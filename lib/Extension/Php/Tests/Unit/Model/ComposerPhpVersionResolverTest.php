<?php

namespace Phpactor\Extension\Php\Tests\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\Extension\Php\Model\ComposerPhpVersionResolver;
use Phpactor\Extension\Php\Tests\IntegrationTestCase;

class ComposerPhpVersionResolverTest extends IntegrationTestCase
{
    #[DataProvider('provideRequireVersion')]
    public function testFromRequireVersion(string $version, string $expected): void
    {
        $this->workspace()->reset();
        $this->workspace()->loadManifest(
            <<<EOT
                // File: composer.json
                {
                    "require": {
                        "php": "$version"
                    }
                }
                EOT
        );
        $resolver = new ComposerPhpVersionResolver($this->workspace()->path('/composer.json'));
        self::assertEquals($expected, $resolver->resolve());
    }

    public static function provideRequireVersion(): Generator
    {
        yield [ '^7.0', '7.0' ];
        yield [ '7.0', '7.0' ];
        yield [ '^7.0 || ^8.0', '7.0' ];
        yield [ '^8.0 || ^7.0', '7.0' ];
        yield [ '^8.0 || 5.3 || ^7.0', '5.3' ];
        yield [ '~8.0', '8.0' ];
        yield [ '8.0 | 7.1 | 1 | 2', '1' ];
    }

    public function testReturnsPlatformWithHigherPrio(): void
    {
        $this->workspace()->reset();
        $this->workspace()->loadManifest(
            <<<'EOT'
                // File: composer.json
                {
                    "require": {
                        "php": "^7.1"
                    },
                    "config": {
                        "platform": {
                            "php": "7.3"
                        }
                    }
                }
                EOT
        );
        $resolver = new ComposerPhpVersionResolver($this->workspace()->path('/composer.json'));
        self::assertEquals('7.3', $resolver->resolve());
    }
}
