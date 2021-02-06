<?php

namespace Phpactor\Extension\CodeTransformExtra\Application;

use Phpactor\CodeTransform\Domain\ClassName;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Phpactor;
use Phpactor\Extension\Core\Application\Helper\FilesystemHelper;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Webmozart\Glob\Glob;
use RuntimeException;

class ClassInflect extends AbstractClassGenerator
{
    public function generateFromExisting(string $srcPath, string $dest, string $variant = 'default', bool $overwrite = false): array
    {
        $srcPath = Phpactor::normalizePath($srcPath);
        $newCodes = [];

        if (false === Glob::isDynamic($srcPath) && false === file_exists($srcPath)) {
            throw new RuntimeException(sprintf(
                'File "%s" does not exist',
                $srcPath
            ));
        }

        foreach (FilesystemHelper::globSourceDestination($srcPath, $dest) as $globSrc => $globDest) {
            if (false === is_file($globSrc)) {
                continue;
            }

            try {
                $newCodes[] = $this->doGenerateFromExisting($globSrc, $globDest, $variant, $overwrite);
            } catch (NotFound $e) {
                $this->logger()->error($e->getMessage());
            }
        }

        return $newCodes;
    }

    private function doGenerateFromExisting(string $src, string $dest, string $variant, bool $overwrite): SourceCode
    {
        $srcClassName = $this->normalizer->normalizeToClass($src);
        $destClassName = $this->normalizer->normalizeToClass($dest);

        $code = $this->generators->get($variant)->generateFromExisting(
            ClassName::fromString((string) $srcClassName),
            ClassName::fromString((string) $destClassName)
        );

        $filePath = $this->normalizer->normalizeToFile($destClassName);
        $code = $code->withPath($filePath);

        $this->writeFile($filePath, (string) $code, $overwrite);

        return $code;
    }
}
