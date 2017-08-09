<?php

namespace Phpactor\Application;

use Phpactor\CodeTransform\Domain\ClassName;

class ClassNew extends AbstractClassGenerator
{
    public function generate(string $src, string $variant = 'default', bool $overwrite = false): string
    {
        $className = $this->normalizer->normalizeToClass($src);

        $code = $this->generators->get($variant)->generateNew(ClassName::fromString((string) $className));
        $filePath = $this->normalizer->normalizeToFile($className);

        $this->writeFile($filePath, (string) $code, $overwrite);

        return $filePath;
    }
}
