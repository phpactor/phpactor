<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor;

use Phpactor\ClassFileConverter\Domain\ClassToFile;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\ClassFileConverter\Domain\ClassName;

class ClassToFileSourceLocator implements SourceCodeLocator
{
    private ClassToFile $converter;

    public function __construct(ClassToFile $converter)
    {
        $this->converter = $converter;
    }
    
    public function locate(Name $name): SourceCode
    {
        $candidates = $this->converter->classToFileCandidates(ClassName::fromString((string) $name));

        if ($candidates->noneFound()) {
            throw new SourceNotFound(sprintf('Could not locate a candidate for "%s"', (string) $name));
        }

        foreach ($candidates as $candidate) {
            if (file_exists((string) $candidate)) {
                return SourceCode::fromPath((string) $candidate);
            }
        }

        throw new SourceNotFound($name);
    }
}
