<?php

namespace Phpactor\Filesystem\Tests\Unit\Domain;

use Phpactor\Filesystem\Domain\FileList;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\Filesystem\Tests\IntegrationTestCase;
use SplFileInfo;

class FileListTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    /**
     * @testdox It returns true if it contains a file path.
     */
    public function testContains(): void
    {
        $list = FileList::fromFilePaths([
            FilePath::fromString('/Foo/Bar.php'),
            FilePath::fromString('/Foo/Foo.php'),
        ]);

        $this->assertTrue($list->contains(FilePath::fromString('/Foo/Bar.php')));
    }

    /**
     * @testdox It returns files within a path
     */
    public function testWithin(): void
    {
        $list = FileList::fromFilePaths([
            FilePath::fromString('/Foo/Bar.php'),
            FilePath::fromString('/Foo/Foo.php'),
            FilePath::fromString('/Boo/Bar.php'),
            FilePath::fromString('/Foo.php'),
        ]);
        $expected = FileList::fromFilePaths([
            FilePath::fromString('/Foo/Bar.php'),
            FilePath::fromString('/Foo/Foo.php'),
        ]);

        $this->assertEquals(
            iterator_to_array($expected),
            iterator_to_array($list->within(FilePath::fromString('/Foo')))
        );
    }

    /**
     * It returns all PHP files with given name (including extension).
     */
    public function testNamed(): void
    {
        $list = FileList::fromFilePaths([
            FilePath::fromString('/reports/Foo/Bar.php.html'),
            FilePath::fromString('/reports/Foo/Foo.php.html'),
            FilePath::fromString('/reports/Boo/Bar.php.html'),
            FilePath::fromString('/reports/Foo.php.html'),
            FilePath::fromString('/Foo/Bar.php'),
            FilePath::fromString('/Foo/Foo.php'),
            FilePath::fromString('/Boo/Bar.php'),
            FilePath::fromString('/Foo.php'),
        ]);
        $expected = FileList::fromFilePaths([
            FilePath::fromString('/Foo/Bar.php'),
            FilePath::fromString('/Boo/Bar.php'),
        ]);

        $this->assertEquals(
            array_values(iterator_to_array($expected)),
            array_values(iterator_to_array($list->named('Bar.php')))
        );
    }

    public function testCallback(): void
    {
        $list = FileList::fromFilePaths([
            FilePath::fromString('/Foo/Bar.php'),
            FilePath::fromString('/Foo/Foo.php'),
            FilePath::fromString('/Boo/Bar.php'),
            FilePath::fromString('/Foo.php'),
        ]);
        $expected = FileList::fromFilePaths([
            FilePath::fromString('/Foo/Bar.php'),
            FilePath::fromString('/Boo/Bar.php'),
        ]);

        $this->assertEquals(
            array_values(iterator_to_array($expected)),
            array_values(iterator_to_array($list->filter(function (SplFileInfo $file) {
                return $file->getFileName() == 'Bar.php';
            })))
        );
    }

    public function testExisting(): void
    {
        $list = FileList::fromFilePaths([
            FilePath::fromString(__FILE__),
            FilePath::fromString('/Foo.php'),
        ]);
        $expected = FileList::fromFilePaths([
            FilePath::fromString(__FILE__),
        ]);

        $this->assertEquals(
            array_values(iterator_to_array($expected)),
            array_values(iterator_to_array($list->existing()))
        );
    }

    public function testExcludesFilesMatchingPatterns(): void
    {
        $list = FileList::fromFilePaths([
            FilePath::fromString('/vendor/foo/bar/tests/bartest.php'),
            FilePath::fromString('/vendor/foo/bar/tests/footest.php'),
            FilePath::fromString('/vendor/foo/bar/src/bar.php'),
            FilePath::fromString('/vendor/foo/bar/src/foo.php'),
        ]);

        self::assertCount(2, $list->excludePatterns([
            '/vendor/**/tests/*',
        ]));
    }

    public function testIncldesFilesMatchingPatterns(): void
    {
        $list = FileList::fromFilePaths([
            FilePath::fromString('/vendor/foo/bar/tests/bartest.php'),
            FilePath::fromString('/vendor/foo/bar/tests/footest.php'),
            FilePath::fromString('/vendor/foo/bar/src/bar.php'),
            FilePath::fromString('/vendor/foo/bar/src/foo.php'),
        ]);

        self::assertCount(2, $list->excludePatterns([
            '/vendor/**/tests/*',
        ]));
    }

    public function testIncludesExcludePatterns(): void
    {

        $list = FileList::fromFilePaths([
            FilePath::fromString('/vendor/cache/important/bartest.php'),
            FilePath::fromString('/vendor/cache/important/footest.php'),
            FilePath::fromString('/vendor/cache/bar.php'),
            FilePath::fromString('/vendor/cache/foo.php'),
        ])->includeAndExclude(
            includePatterns: [ '/src/**/*', '/vendor/cache/important/**/*'],
            excludePatterns: [ '/vendor/**/*' ],
        );

        self::assertCount(2, $list);
    }

    public function testContainingString(): void
    {
        $this->workspace()->put('one', 'one two three');
        $this->workspace()->put('two', 'four five six');

        $list = FileList::fromFilePaths([
            FilePath::fromString($this->workspace()->path('one')),
            FilePath::fromString($this->workspace()->path('two'))
        ]);

        self::assertCount(2, $list);
        self::assertCount(1, $list->containingString('one'));
        self::assertCount(1, $list->containingString('two'));
        self::assertCount(1, $list->containingString('four'));
        self::assertCount(0, $list->containingString('seven'));
    }

    public function testContainingStringFileNotExisting(): void
    {
        $list = FileList::fromFilePaths([
            FilePath::fromString($this->workspace()->path('one')),
            FilePath::fromString($this->workspace()->path('two'))
        ]);

        self::assertCount(2, $list);
        self::assertCount(0, $list->containingString('one'));
    }
}
