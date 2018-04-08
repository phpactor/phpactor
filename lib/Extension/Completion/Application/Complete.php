<?php

namespace Phpactor\Extension\Completion\Application;

use Phpactor\Application\Helper\FilesystemHelper;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\SymbolContext;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Reflector;
use Phpactor\Completion\Core\Completor;

class Complete
{
    /**
     * @var Completor
     */
    private $competor;

    public function __construct(Completor $competor)
    {
        $this->competor = $competor;
    }

    public function complete(string $source, int $offset): array
    {
        $result = $this->competor->complete($source, $offset);

        return [
            'suggestions' => $result->suggestions()->toArray(),
            'issues' => $result->issues()->toArray(),
        ];
    }
}
