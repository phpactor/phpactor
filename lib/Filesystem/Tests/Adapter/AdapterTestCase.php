<?php

namespace Phpactor\Filesystem\Tests\Adapter;

use Phpactor\Filesystem\Domain\Filesystem;

abstract class AdapterTestCase extends IntegrationTestCase
{
    public function setUp(): void
    {
        $this->initWorkspace();
        $this->loadProject();
    }

    public function testFind(): void
    {
        $fileList = $this->filesystem()->fileList();
        $this->assertTrue($fileList->contains($this->filesystem()->createPath('src/Foobar.php')));

        $location = $this->filesystem()->createPath('src/Hello/Goodbye.php');
        $foo = $fileList->contains($location);
        $this->assertTrue($foo);
    }

    public function testRemove(): void
    {
        $file = $this->filesystem()->createPath('src/Hello/Goodbye.php');
        $this->assertTrue(file_exists($file->path()));
        $this->filesystem()->remove($file);
        $this->assertFalse(file_exists($file->path()));
    }

    public function testRemoveDirectory(): void
    {
        $file = $this->filesystem()->createPath('src/Hello');
        $this->assertTrue(file_exists($file->path()));
        $this->filesystem()->remove($file);
        $this->assertFalse(file_exists($file->path()));
    }

    public function testMove(): void
    {
        $srcLocation = $this->filesystem()->createPath('src/Hello/Goodbye.php');
        $destLocation = $this->filesystem()->createPath('src/Hello/Hello.php');

        $this->filesystem()->move($srcLocation, $destLocation);
        $this->assertTrue(file_exists($destLocation->path()));
        $this->assertFalse(file_exists($srcLocation->path()));
    }

    public function testMoveDirectory(): void
    {
        $srcLocation = $this->filesystem()->createPath('src/Hello');
        $destLocation = $this->filesystem()->createPath('src/Goodbye');

        $this->filesystem()->move($srcLocation, $destLocation);
        $this->assertTrue(file_exists($destLocation->path()));
        $this->assertFalse(file_exists($srcLocation->path()));

        $testFile = $this->filesystem()->createPath('src/Goodbye/Goodbye.php');
        $this->assertTrue(file_exists($testFile->path()));
    }

    public function testCopy(): void
    {
        $srcLocation = $this->filesystem()->createPath('src/Hello/Goodbye.php');
        $destLocation = $this->filesystem()->createPath('src/Hello/Hello.php');

        $this->filesystem()->copy($srcLocation, $destLocation);
        $this->assertTrue(file_exists($destLocation->path()));
        $this->assertTrue(file_exists($srcLocation->path()));
    }

    public function testExists(): void
    {
        $path = $this->filesystem()->createPath('src/Hello/Goodbye.php');
        $this->assertTrue($this->filesystem()->exists($path));

        $path = $this->filesystem()->createPath('src/Hello/Plop.php');
        $this->assertFalse($this->filesystem()->exists($path));
    }

    public function testCopyRecursive(): void
    {
        $srcLocation = $this->filesystem()->createPath('src');
        $destLocation = $this->filesystem()->createPath('src/AAAn');

        $list = $this->filesystem()->copy($srcLocation, $destLocation);
        $this->assertTrue(file_exists($destLocation->path()));
        $this->assertTrue(file_exists($srcLocation->path()));
        $this->assertTrue(file_exists($srcLocation->path() . '/AAAn/Foobar.php'));
        $this->assertTrue(file_exists($srcLocation->path() . '/AAAn/Hello/Goodbye.php'));
        $this->assertCount(2, $list->srcFiles());
        $this->assertCount(2, $list->destFiles());
    }

    public function testWriteGet(): void
    {
        $path = $this->filesystem()->createPath('src/Hello/Goodbye.php');

        $this->filesystem()->writeContents($path, 'foo');
        $this->assertEquals('foo', $this->filesystem()->getContents($path));
    }

    abstract protected function filesystem(): Filesystem;
}
