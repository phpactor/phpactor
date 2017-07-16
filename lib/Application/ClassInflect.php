<?php

namespace Phpactor\Application;

use Phpactor\CodeTransform\Domain\GenerateNew;
use Phpactor\Application\Helper\ClassFileNormalizer;
use Phpactor\CodeTransform\Domain\ClassName;
use Phpactor\CodeTransform\Domain\Generators;
use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\Filesystem\Domain\FilePath;

class ClassInflect extends AbstractClassGenerator
{
    public function generateFromExisting(string $src, string $dest, string $variant = 'default', bool $overwrite = false): string
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
