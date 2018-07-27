<?php

namespace Phpactor\Tests\Unit\Extension\ClassMover\Application\Finder;

use Closure;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\ClassMover\Application\Finder\FileFinder;
use Phpactor\Filesystem\Domain\FileList;
use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\ReflectorBuilder;
use Prophecy\Argument;
use RuntimeException;

class FileFinderTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $filesystem;

    /**
     * @var ObjectProphecy
     */
    private $fileList;

    public function setUp()
    {
        $this->filesystem = $this->prophesize(Filesystem::class);
        $this->fileList = $this->prophesize(FileList::class);
    }

    public function testReturnsAllPhpFilesIfNoMemberNameGiven()
    {
        $this->setupAllFiles();
        $class = $this->reflectClass('class Foobar {}', 'Foobar');
        $files = $this->filesFor($class, null);
        $this->assertEquals($this->fileList->reveal(), $files);
    }

    public function testReturnsAllPhpFilesIfNoClassReflectionGiven()
    {
        $this->setupAllFiles();
        $files = $this->filesFor(null, null);
        $this->assertEquals($this->fileList->reveal(), $files);
    }

    public function testThrowsExceptionIfClassHasNoMembersByName()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Class has no member named "foobar"');
        $this->setupAllFiles();

        $class = $this->reflectClass('class Foobar { public function bar() {} }', 'Foobar');
        $files = $this->filesFor($class, 'foobar');
        $this->assertEquals($this->fileList->reveal(), $files);
    }

    public function testReturnsAllPhpFilesFilteredByMemberIfMemberIsPublic()
    {
        $this->setupAllFiles();
        $class = $this->reflectClass('class Foobar { public function abcde() {} }', 'Foobar');
        $this->fileList->filter(Argument::type(Closure::class))->willReturn($this->fileList->reveal());
        $files = $this->filesFor($class, 'abcde');
        $this->assertEquals($this->fileList->reveal(), $files);
    }

    public function testReturnsClassAndTraitFilePathsIfMemberIsPrivate()
    {
        $class = $this->reflectClass(
            SourceCode::fromPathAndString('barfoo', 'trait Barbar {} class Foobar { use Barbar; private function foobar(){} }'),
            'Foobar'
        );
        $files = $this->filesFor($class, 'foobar');
        $this->assertEquals(FileList::fromFilePaths(['barfoo', 'barfoo'], $files), $files);
    }

    public function testParentsTraitsAndInterfacesIfMemberIsProtected()
    {
        $class = $this->reflectClass(
            SourceCode::fromPathAndString('barfoo', 'interface Inter1 {} class ParentClass {} trait Barbar {} class Foobar extends ParentClass implements Inter1 { use Barbar; protected function foobar(){} }'),
            'Foobar'
        );
        $files = $this->filesFor($class, 'foobar');
        $this->assertEquals(FileList::fromFilePaths(['barfoo', 'barfoo', 'barfoo', 'barfoo'], $files), $files);
    }

    private function filesFor(ReflectionClassLike $class = null, string $memberName = null)
    {
        return (new FileFinder())->filesFor($this->filesystem->reveal(), $class, $memberName);
    }

    private function setupAllFiles()
    {
        $this->filesystem->fileList()->willReturn($this->fileList->reveal());
        $this->fileList->existing()->willReturn($this->fileList->reveal());
        $this->fileList->phpFiles()->willReturn($this->fileList->reveal());
    }

    private function reflectClass($source, string $name)
    {
        $builder = ReflectorBuilder::create()->addSource(SourceCode::fromPathAndString('foobar', '<?php ' . $source));
        return $builder->build()->reflectClassLike($name);
    }
}
