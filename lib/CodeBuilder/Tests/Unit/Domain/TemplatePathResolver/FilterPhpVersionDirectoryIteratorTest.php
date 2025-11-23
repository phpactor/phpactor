<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\TemplatePathResolver;

use PHPUnit\Framework\Attributes\DataProvider;
use ArrayIterator;
use Iterator;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\TemplatePathResolver\FilterPhpVersionDirectoryIterator;
use Prophecy\PhpUnit\ProphecyTrait;
use SplFileInfo;

class FilterPhpVersionDirectoryIteratorTest extends TestCase
{
    use ProphecyTrait;

    #[DataProvider('provideDirectoriesToFilter')]
    public function testThatItKeepsOnlyDirectoriesOfInferiorOrEqualVersion(
        Iterator $iterator,
        string $phpVersion,
        array $expectedFilteredDirectories
    ): void {
        $filteredDirectories = \iterator_to_array(
            new FilterPhpVersionDirectoryIterator($iterator, $phpVersion)
        );

        $this->assertEqualsCanonicalizing(
            $expectedFilteredDirectories,
            $filteredDirectories
        );
    }

    public function provideDirectoriesToFilter(): iterable
    {
        $directories = new ArrayIterator([
            $this->createFakeFile('a-file'),
            $php72 = $this->createFakeDirectory('7.2'),
            $this->createFakeDirectory('a-directory'),
            $php74 = $this->createFakeDirectory('7.4'),
            $php74Special = $this->createFakeDirectory('7.4-special'),
        ]);

        yield 'For PHP 7.3' => [$directories, '7.3.3', [$php72]];
        yield 'For PHP 7.4' => [$directories, '7.4.0', [$php72, $php74, $php74Special]];
    }

    private function createFakeFile(string $filename): SplFileInfo
    {
        return $this->createFakeSplFileInfo($filename, false);
    }

    private function createFakeDirectory(string $filename): SplFileInfo
    {
        return $this->createFakeSplFileInfo($filename, true);
    }

    private function createFakeSplFileInfo(string $filename, bool $isDir = false): SplFileInfo
    {
        $file = $this->prophesize(\Symfony\Component\Finder\SplFileInfo::class);
        $file->getFilename()->willReturn($filename);
        $file->isDir()->willReturn($isDir);

        return $file->reveal();
    }
}
