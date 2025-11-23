<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\SourceCodeFilesystem;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\ClassFileConverter\Adapter\Simple\SimpleFileToClass;
use Phpactor\Completion\Bridge\TolerantParser\Qualifier\ClassQualifier;
use Phpactor\Completion\Bridge\TolerantParser\SourceCodeFilesystem\ScfClassCompletor;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\TolerantCompletorTestCase;
use Phpactor\Filesystem\Adapter\Simple\SimpleFilesystem;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\TextDocument\TextDocument;

class ScfClassCompletorTest extends TolerantCompletorTestCase
{
    #[DataProvider('provideComplete')]
    public function testComplete(string $source, array $expected): void
    {
        $this->assertComplete($source, $expected);
    }

    public static function provideComplete(): Generator
    {
        yield 'extends' => [
            '<?php class Foobar extends <>',
            [
                [
                    'type' => Suggestion::TYPE_CLASS,
                    'name' => 'Alphabet',
                    'short_description' => 'Test\Name\Alphabet',
                    'range' => [ 27, 27 ],
                ],
                [
                    'type' => Suggestion::TYPE_CLASS,
                    'name' => 'Backwards',
                    'short_description' => 'Test\Name\Backwards',
                ],
                [
                    'type' => Suggestion::TYPE_CLASS,
                    'name' => 'Clapping',
                    'short_description' => 'Test\Name\Clapping',
                ],
                [
                    'type' => Suggestion::TYPE_CLASS,
                    'name' => 'WithoutNS',
                    'short_description' => 'WithoutNS',
                ],
            ],
        ];

        yield 'extends partial' => [
            '<?php class Foobar extends Cl<>',
            [
                [
                    'type' => Suggestion::TYPE_CLASS,
                    'name' => 'Clapping',
                    'short_description' => 'Test\Name\Clapping',
                    'range' => [ 27, 29 ],
                ],
            ],
        ];

        yield 'extends keyword with subsequent code' => [
            '<?php class Foobar extends Cl<> { }',
            [
                [
                    'type' => Suggestion::TYPE_CLASS,
                    'name' => 'Clapping',
                    'short_description' => 'Test\Name\Clapping',
                ],
            ],
        ];

        yield 'new keyword' => [
            '<?php new <>',
            [
                [
                    'type' => Suggestion::TYPE_CLASS,
                    'name' => 'Alphabet',
                    'short_description' => 'Test\Name\Alphabet',
                ],
                [
                    'type' => Suggestion::TYPE_CLASS,
                    'name' => 'Backwards',
                    'short_description' => 'Test\Name\Backwards',
                ],
                [
                    'type' => Suggestion::TYPE_CLASS,
                    'name' => 'Clapping',
                    'short_description' => 'Test\Name\Clapping',
                ],
                [
                    'type' => Suggestion::TYPE_CLASS,
                    'name' => 'WithoutNS',
                    'short_description' => 'WithoutNS',
                ],
            ],
        ];

        yield 'new keyword with partial' => [
            '<?php new Cla<>',
            [
                [
                    'type' => Suggestion::TYPE_CLASS,
                    'name' => 'Clapping',
                    'short_description' => 'Test\Name\Clapping',
                ],
            ],
        ];

        yield 'use keyword' => [
            '<?php use <>',
            [
                [
                    'type' => Suggestion::TYPE_CLASS,
                    'name' => 'Alphabet',
                    'short_description' => 'Test\Name\Alphabet',
                ],
                [
                    'type' => Suggestion::TYPE_CLASS,
                    'name' => 'Backwards',
                    'short_description' => 'Test\Name\Backwards',
                ],
                [
                    'type' => Suggestion::TYPE_CLASS,
                    'name' => 'Clapping',
                    'short_description' => 'Test\Name\Clapping',
                ],
                [
                    'type' => Suggestion::TYPE_CLASS,
                    'name' => 'WithoutNS',
                    'short_description' => 'WithoutNS',
                ],
            ],
        ];

        yield 'use keyword with partial' => [
            '<?php use Cla<>',
            [
                [
                    'type' => Suggestion::TYPE_CLASS,
                    'name' => 'Clapping',
                    'short_description' => 'Test\Name\Clapping',
                ],
            ],
        ];
    }

    #[DataProvider('provideImportClass')]
    public function testImportClass($source, $expected): void
    {
        $this->assertComplete($source, $expected);
    }

    public static function provideImportClass(): Generator
    {
        yield 'does not import from the root namespace when in the root namespace' => [
            '<?php Without<>',
            [
                [
                    'name' => 'WithoutNS',
                    'class_import' => null,
                ],
            ],
        ];

        yield 'does not import when candidate class is in the same namespace' => [
            '<?php namespace Test\Name; class Foobar extends Alphabe<>',
            [
                [
                    'name' => 'Alphabet',
                    'class_import' => null,
                ],
            ],
        ];

        yield 'does not import when candidate class is already imported' => [
            '<?php use Test\Name\Alphabet; Alpha<>',
            [
                [
                    'name' => 'Alphabet',
                    'class_import' => null
                ],
            ],
        ];

        yield 'when candidate class is in different namespace' => [
            '<?php namespace Foobar; Alpha<>',
            [
                [
                    'name' => 'Alphabet',
                    'class_import' => 'Test\Name\Alphabet',
                ],
            ],
        ];

        yield 'when the candidate class is in the root namespace' => [
            '<?php namespace Foobar; WithoutN<>',
            [
                [
                    'name' => 'WithoutNS',
                    'class_import' => 'WithoutNS',
                ],
            ],
        ];
    }

    protected function createTolerantCompletor(TextDocument $source): TolerantCompletor
    {
        $filesystem = new SimpleFilesystem(FilePath::fromString(__DIR__ . '/files'));
        $fileToClass = new SimpleFileToClass();

        return new ScfClassCompletor($filesystem, $fileToClass, new ClassQualifier(0));
    }
}
