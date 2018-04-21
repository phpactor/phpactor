<?php

namespace Phpactor\Extension\ClassMover\Application\Finder;

use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Visibility;

class FileFinder
{
    public function filesFor(Filesystem $filesystem, ReflectionClassLike $reflection, string $memberName = null, string $memberType = null)
    {
        // if no member name, then we are searching for all members of the
        // class, and we can't really optimise this...
        if (null === $memberName) {
            return $this->allPhpFiles($filesystem);
        }

        $members = $reflection->members();
        if ($members->count() === 0) {
            throw new RuntimeException(sprintf(
                'Class has no member named "%s", has the following member names: "%s"',
                implode('", "', $members->keys())
            ));
        }

        $members = $members->byName()->byVisibilities([
            Visibility::public()
        ]);

        if (
            false === $reflection instanceof ReflectionClass &&
            $members->count() > 1
        ) {
            // we have public members or a non-class, we need to search the
            // whole tree, but we can discount any files which do not contain
            // the member name string.
            return $this->allPhpFiles()->filter(function (SplFileInfo $file) use ($memberName) {
                return preg_match('{' . $memberName . '}', file_get_contents($file->getPathname()));
            });
        }

        /** @var ReflectionMember $member */
        $private = false;
        foreach ($members as $member) {
            if ($member->visibility() === Visibility::private()) {
                $private = true;
            }
        }

        return $this->pathsFromReflectionClass($reflection, $private);
    }

    private function pathsFromReflectionClass(ReflectionClass $reflection, bool $private)
    {
        $filePaths = array_map(function (ReflectionTrait $trait) {
            return $trait->sourceCode()->path();
        }, iterator_to_array($reflection->traits()));
        $filePaths[] = $reflection->sourceCode()->path();
        
        if ($private) {
            return $filePaths;
        }
        
        while ($parent = $reflection->parent()) {
            $filePaths[] = $parent->sourceCode()->path();
        }
        
        foreach ($reflection->interfaces() as $interface) {
            $filePaths[] = $interface->sourceCode()->path();
        }
        
        return $filePaths;
    }

    private function allPhpFiles(Filesystem $filesystem)
    {
        $filePaths = $filesystem->fileList()->existing()->phpFiles();
        return $filePaths;
    }
}
