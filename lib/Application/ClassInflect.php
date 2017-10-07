<?php

namespace Phpactor\Application;

use Phpactor\CodeTransform\Domain\ClassName;
use Phpactor\Phpactor;
use Phpactor\Application\Helper\FilesystemHelper;
use Phpactor\WorseReflection\Core\Exception\NotFound;

class ClassInflect extends AbstractClassGenerator
{
    public function generateFromExisting(string $src, string $dest, string $variant = 'default', bool $overwrite = false): array
    {
        $src = Phpactor::normalizePath($src);
        $newPaths = [];
        foreach (FilesystemHelper::globSourceDestination($src, $dest) as $globSrc => $globDest) {
            if (false === is_file($globSrc)) {
                continue;
            }

            try {
                $this->doGenerateFromExisting($globSrc, $globDest, $variant, $overwrite);
            } catch (NotFound $e) {
                $this->logger()->error($e->getMessage());
            }
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
