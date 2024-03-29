<?php

namespace Phpactor\Tests\Unit\Extension\ClassMover\Application\Finder;

use Closure;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\ClassMover\Application\Finder\FileFinder;
use Phpactor\Filesystem\Domain\FileList;
use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\ReflectorBuilder;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use RuntimeException;
use Phpactor\TextDocument\TextDocument;

class FileFinderTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<Filesystem>
     */
    private ObjectProphecy $filesystem;

    /**
    * @var ObjectProphecy<FileList>
    */
    private ObjectProphecy $fileList;

    public function setUp(): void
    {
        $this->filesystem = $this->prophesize(Filesystem::class);
        $this->fileList = $this->prophesize(FileList::class);
    }

    public function testReturnsAllPhpFilesIfNoMemberNameGiven(): void
    {
        $this->setupAllFiles();
        $class = $this->reflectClass('class Foobar {}', 'Foobar');
        $files = $this->filesFor($class, null);
        $this->assertEquals($this->fileList->reveal(), $files);
    }

    public function testReturnsAllPhpFilesIfNoClassReflectionGiven(): void
    {
        $this->setupAllFiles();
        $files = $this->filesFor(null, null);
        $this->assertEquals($this->fileList->reveal(), $files);
    }

    public function testThrowsExceptionIfClassHasNoMembersByName(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Class has no member named "foobar"');
        $this->setupAllFiles();

        $class = $this->reflectClass('class Foobar { public function bar() {} }', 'Foobar');
        $files = $this->filesFor($class, 'foobar');
        $this->assertEquals($this->fileList->reveal(), $files);
    }

    public function testReturnsAllPhpFilesFilteredByMemberIfMemberIsPublic(): void
    {
        $this->setupAllFiles();
        $class = $this->reflectClass('class Foobar { public function abcde() {} }', 'Foobar');
        $this->fileList->filter(Argument::type(Closure::class))->willReturn($this->fileList->reveal());
        $files = $this->filesFor($class, 'abcde');
        $this->assertEquals($this->fileList->reveal(), $files);
    }

    public function testReturnsClassAndTraitFilePathsIfMemberIsPrivate(): void
    {
        $class = $this->reflectClass(
            TextDocumentBuilder::create('<?php trait Barbar {} class Foobar { use Barbar; private function foobar(){} }')->uri('file:///barfoo.php')->build(),
            'Foobar'
        );
        $files = $this->filesFor($class, 'foobar');
        $this->assertEquals(FileList::fromFilePaths(['barfoo', 'barfoo']), $files);
    }

    public function testParentsTraitsAndInterfacesIfMemberIsProtected(): void
    {
        $class = $this->reflectClass(
            TextDocumentBuilder::create('<?php interface Inter1 {} class ParentClass {} trait Barbar {} class Foobar extends ParentClass implements Inter1 { use Barbar; protected function foobar(){} }')->uri('file:///barfoo')->build(),
            'Foobar'
        );
        $files = $this->filesFor($class, 'foobar');
        $this->assertEquals(FileList::fromFilePaths(['barfoo', 'barfoo', 'barfoo', 'barfoo']), $files);
    }

    private function filesFor(ReflectionClassLike $class = null, string $memberName = null): FileList
    {
        return (new FileFinder())->filesFor($this->filesystem->reveal(), $class, $memberName);
    }

    private function setupAllFiles(): void
    {
        $this->filesystem->fileList()->willReturn($this->fileList->reveal());
        $this->fileList->existing()->willReturn($this->fileList->reveal());
        $this->fileList->phpFiles()->willReturn($this->fileList->reveal());
    }

    private function reflectClass(string|TextDocument $source, string $name): ReflectionClassLike
    {
        if (is_string($source)) {
            $source = '<?php ' . $source;
        }
        $builder = ReflectorBuilder::create()->addSource(TextDocumentBuilder::fromUnknown($source));
        return $builder->build()->reflectClassLike($name);
    }
}
