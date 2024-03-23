<?php

namespace Phpactor\Extension\ClassMover\Application\Finder;

use Phpactor\Filesystem\Domain\FileList;
use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Visibility;
use RuntimeException;
use SplFileInfo;

class FileFinder
{
    public function filesFor(Filesystem $filesystem, ReflectionClassLike $reflection = null, string $memberName = null): FileList
    {
        // if no member name, then we are searching for all members of the
        // class, and we can't really optimise this...
        if (null === $reflection || null === $memberName) {
            return $this->allPhpFiles($filesystem);
        }

        $members = $reflection->members();
        if ($members->byName($memberName)->count() === 0) {
            throw new RuntimeException(sprintf(
                'Class has no member named "%s", has the following member names: "%s"',
                $memberName,
                implode('", "', $members->keys())
            ));
        }

        $publicMembers = $members->byName($memberName)->byVisibilities([
            Visibility::public()
        ]);

        if (
            false === $reflection instanceof ReflectionClass ||
            $publicMembers->count() > 0
        ) {
            // we have public members or a non-class, we need to search the
            // whole tree, but we can discount any files which do not contain
            // the member name string.
            return $this->allPhpFiles($filesystem)->filter(function (SplFileInfo $file) use ($memberName): bool {
                return preg_match('{' . $memberName . '}', file_get_contents($file->getPathname())) === 1;
            });
        }

        /** @var ReflectionMember $member */
        $private = false;
        foreach ($members as $member) {
            if ($member->visibility() == Visibility::private()) {
                $private = true;
            }
        }

        return $this->pathsFromReflectionClass($reflection, $private);
    }

    private function pathsFromReflectionClass(ReflectionClass $reflection, bool $private): FileList
    {
        $path = $reflection->sourceCode()->uri()?->path();

        if (!$path) {
            throw new RuntimeException(
                sprintf('Source class "%s" has no path associated with it', $reflection->name()),
            );
        }

        $filePaths = [ $path ];
        $filePaths = $this->traitFilePaths($reflection, $filePaths);

        if ($private) {
            return FileList::fromFilePaths($filePaths);
        }

        $filePaths = $this->parentFilePaths($reflection, $filePaths);
        $filePaths = $this->interfaceFilePaths($reflection, $filePaths);

        return FileList::fromFilePaths($filePaths);
    }

    private function allPhpFiles(Filesystem $filesystem): FileList
    {
        return $filesystem->fileList()->existing()->phpFiles();
    }

    /**
     * @param array<string> $filePaths
     *
     * @return array<string>
     */
    private function parentFilePaths(ReflectionClass $reflection, array $filePaths): array
    {
        $context = $reflection->parent();
        while ($context) {
            $path = $context->sourceCode()->uri()?->path();
            if ($path === null) {
                continue;
            }
            $filePaths[] = $path;
            $context = $context->parent();
        }

        return $filePaths;
    }

    /**
     * @param array<string> $filePaths
     *
     * @return array<string>
     */
    private function traitFilePaths(ReflectionClass $reflection, array $filePaths): array
    {
        foreach ($reflection->traits() as $trait) {
            $path = $trait->sourceCode()->uri()?->path();
            if ($path === null) {
                continue;
            }
            $filePaths[] = $path;
        }
        return $filePaths;
    }

    /**
     * @param array<string> $filePaths
     *
     * @return array<string>
     */
    private function interfaceFilePaths(ReflectionClass $reflection, array $filePaths): array
    {
        foreach ($reflection->interfaces() as $interface) {
            $path = $interface->sourceCode()->uri()?->path();
            if ($path === null) {
                continue;
            }
            $filePaths[] = $path;
        }

        return $filePaths;
    }
}
