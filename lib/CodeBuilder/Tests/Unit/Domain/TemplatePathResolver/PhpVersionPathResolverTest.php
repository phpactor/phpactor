<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\TemplatePathResolver;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\CodeBuilder\Domain\TemplatePathResolver\PhpVersionPathResolver;
use Phpactor\CodeBuilder\Tests\IntegrationTestCase;

class PhpVersionPathResolverTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    #[DataProvider('provideResolvePaths')]
    public function testResolvePaths(
        string $phpVersion,
        array $fullTemplatePaths,
        array $templatePaths,
        array $expectedPaths
    ): void {
        foreach ($fullTemplatePaths as $fullTemplatePath) {
            $this->workspace()->mkdir($fullTemplatePath);
        }

        $resolver = new PhpVersionPathResolver($phpVersion);
        self::assertEquals(array_map(function (string $path) {
            return $this->workspace()->path($path);
        }, $expectedPaths), $resolver->resolve(array_map(function (string $path) {
            return $this->workspace()->path($path);
        }, $templatePaths)));
    }

    public static function provideResolvePaths(): Generator
    {
        yield 'none' => [
            '5.6',
            [],
            [],
            []
        ];

        yield 'resolves given path but not the version path' => [
            '7.0',
            [
                '/path1/7.1'
            ],
            [
                '/path1'
            ],
            [
                '/path1',
            ]
        ];

        yield 'priorities the version path if the version matches' => [
            '7.1',
            [
                '/path1/7.1'
            ],
            [
                '/path1'
            ],
            [
                '/path1/7.1',
                '/path1',
            ]
        ];

        yield 'returns previous versions for higher versions' => [
            '7.2',
            [
                '/path1/7.1'
            ],
            [
                '/path1'
            ],
            [
                '/path1/7.1',
                '/path1',
            ]
        ];

        yield 'returns multiple previous versions' => [
            '7.2',
            [
                '/path1/7.0',
                '/path1/7.1',
                '/path1/7.2'
            ],
            [
                '/path1'
            ],
            [
                '/path1/7.2',
                '/path1/7.1',
                '/path1/7.0',
                '/path1',
            ]
        ];
    }
}
