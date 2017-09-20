<?php

namespace Phpactor\Application;

use Phpactor\CodeTransform\Domain\ClassName;
use Phpactor\Phpactor;
use Webmozart\Glob\Glob;
use Webmozart\PathUtil\Path;
use Phpactor\Application\Helper\FilesystemHelper;

class ClassInflect extends AbstractClassGenerator
{
    public function generateFromExisting(string $src, string $dest, string $variant = 'default', bool $overwrite = false): array
    {
        $src = Phpactor::normalizePath($src);
        $newPaths = [];
        foreach (FilesystemHelper::globSourceDestination($src, $dest) as $globSrc => $globDest) {
            $this->doGenerateFromExisting($globSrc, $globDest, $variant, $overwrite);
        }

        return $newPaths;
    }

    private function doGenerateFromExisting(string $src, string $dest, string $variant, bool $overwrite): string
    {
        $srcClassName = $this->normalizer->normalizeToClass($src);
        $destClassName = $this->normalizer->normalizeToClass($dest);

        $code = $this->generators->get($variant)->generateFromExisting(
            ClassName::fromString((string) $srcClassName),
            ClassName::fromString((string) $destClassName)
        );

        $filePath = $this->normalizer->normalizeToFile($destClassName);

        $this->writeFile($filePath, (string) $code, $overwrite);

        return (string) $filePath;
    }
}
