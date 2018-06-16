<?php

namespace Phpactor\Extension\CodeTransform\Application;

use Phpactor\CodeTransform\Domain\ClassName;
use Phpactor\CodeTransform\Domain\SourceCode;

class ClassNew extends AbstractClassGenerator
{
    public function generate(string $src, string $variant = 'default', bool $overwrite = false): SourceCode
    {
        $className = $this->normalizer->normalizeToClass($src);

        $code = $this->generators->get($variant)->generateNew(ClassName::fromString((string) $className));
        $filePath = $this->normalizer->normalizeToFile($className);

        $code = $code->withPath($filePath);

        $this->writeFile($filePath, (string) $code, $overwrite);

        return $code;
    }
}
