<?php

namespace Phpactor\Filesystem\Tests\Adapter\Git;

use Phpactor\Filesystem\Adapter\Git\GitFilesystem;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\Filesystem\Tests\Adapter\AdapterTestCase;
use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\Filesystem\Domain\Cwd;
use RuntimeException;

class GitFilesystemTest extends AdapterTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        chdir($this->workspacePath());
        exec('git init');
        exec('git add *');
    }

    /**
     * It sohuld throw an exception if the cwd does not have a .git folder.
     */
    public function testNoGitFolder(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The cwd does not seem to be');
        new GitFilesystem(FilePath::fromString(__DIR__));
    }

    /**
     * It should fallback to simple filesystem if file is not under VC.
     */
    public function testMoveNonVersionedFile(): void
    {
        touch($this->workspacePath() . '/Test.php');
        $this->filesystem()->move(
            FilePath::fromString($this->workspacePath() . '/Test.php'),
            FilePath::fromString($this->workspacePath() . '/Foobar.php')
        );
        self::assertFileExists($this->workspacePath() . '/Foobar.php');
        self::assertFileDoesNotExist($this->workspacePath() . '/Test.php');
    }

    public function testMoveNonVersionedFileToNonExistingDirectory(): void
    {
        touch($this->workspacePath() . '/Test.php');
        $this->filesystem()->move(
            FilePath::fromString($this->workspacePath() . '/Test.php'),
            FilePath::fromString($this->workspacePath() . '/NotExisting/Foobar.php')
        );
        self::assertFileDoesNotExist($this->workspacePath() . '/Test.php');
        self::assertFileExists($this->workspacePath() . '/NotExisting/Foobar.php');
    }

    /**
     * It should fallback to simple filesystem if file is not under VC.
     */
    public function testRemoveNonVersionedFile(): void
    {
        touch($this->workspacePath() . '/Test.php');
        $this->filesystem()->remove(FilePath::fromString($this->workspacePath() . '/Test.php'));
        self::assertFileDoesNotExist($this->workspacePath() . '/Test.php');
    }

    /**
     * It lists untracked files
     */
    public function testListUntracked(): void
    {
        $path = $this->workspacePath() . '/Test.php';
        touch($path);
        self::assertTrue($this->filesystem()->fileList()->contains(FilePath::fromString($path)));
    }

    protected function filesystem(): Filesystem
    {
        return new GitFilesystem(FilePath::fromString($this->workspacePath()));
    }
}
