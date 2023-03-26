<?php
declare(strict_types=1);

namespace Phpactor\WorseReflection\Core\SourceCodeLocator;

use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use ReflectionFunction;
use InvalidArgumentException;

class NativeReflectionFunctionSourceLocator implements SourceCodeLocator
{
    public function locate(Name $name): TextDocument
    {
        if (function_exists((string) $name)) {
            return $this->sourceFromFunctionName($name);
        }

        throw new SourceNotFound(sprintf(
            'Could not locate function with Reflection: "%s"',
            $name->__toString()
        ));
    }

    private function sourceFromFunctionName(Name $name): TextDocument
    {
        $functionName = (string) $name;
        $function = new ReflectionFunction($functionName);
        $fileName = $function->getFileName();

        if ($function->isInternal()) {
            throw new SourceNotFound(sprintf(
                'Function "%s" is an internal function, there is another locator for that',
                $name->__toString()
            ));
        }
        if ($fileName === false) {
            throw new InvalidArgumentException(sprintf('Function "%s" has no file', $functionName));
        }

        return TextDocument::fromPath($fileName);
    }
}
