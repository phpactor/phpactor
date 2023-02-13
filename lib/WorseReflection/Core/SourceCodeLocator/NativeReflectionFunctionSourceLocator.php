<?php
declare(strict_types=1);

namespace Phpactor\WorseReflection\Core\SourceCodeLocator;

use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use ReflectionFunction;

class NativeReflectionFunctionSourceLocator implements SourceCodeLocator
{
    public function locate(Name $name): SourceCode
    {
        if (function_exists((string) $name)) {
            return $this->sourceFromFunctionName($name);
        }

        throw new SourceNotFound(sprintf(
            'Could not locate function with Reflection: "%s"',
            $name->__toString()
        ));
    }

    private function sourceFromFunctionName(Name $name): SourceCode
    {
        $function = new ReflectionFunction($name->__toString());

        $fileName = $function->getFileName();
        if ($function->isInternal()) {
            throw new SourceNotFound(sprintf(
                'Function "%s" is an internal function, there is another locator for that',
                $name->__toString()
            ));
        }

        if (!$fileName || !file_exists($fileName)) {
            throw new SourceNotFound(sprintf('Unable to locate file for function: "%s"', (string) $name));
        }

        return SourceCode::fromPath($fileName);
    }
}
