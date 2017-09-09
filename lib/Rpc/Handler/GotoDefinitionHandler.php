<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Rpc\Editor\EchoAction;
use Phpactor\Application\OffsetDefinition;
use Phpactor\Rpc\Editor\OpenFileAction;
use Phpactor\Core\GotoDefinition\GotoDefinition;
use Phpactor\WorseReflection\Reflector;
use Phpactor\Application\Helper\ClassFileNormalizer;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Offset;

class GotoDefinitionHandler implements Handler
{
    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var ClassFileNormalizer
     */
    private $classFileNormalizer;

    /**
     * @var GotoDefinition
     */
    private $gotoDefinition;

    public function __construct(
        Reflector $reflector,
        ClassFileNormalizer $classFileNormalizer
    )
    {
        $this->reflector = $reflector;
        $this->classFileNormalizer = $classFileNormalizer;
        $this->gotoDefinition = new GotoDefinition($reflector);
    }

    public function name(): string
    {
        return 'goto_definition';
    }

    public function defaultParameters(): array
    {
        return [
            'offset' => null,
            'path' => null,
        ];
    }

    public function handle(array $arguments)
    {
        // TODO: Pass source or write to temporary file
        $result = $this->reflector->reflectOffset(
            SourceCode::fromPath($arguments['path']),
            Offset::fromInt($arguments['offset'])
        );

        $result = $this->gotoDefinition->gotoDefinition($result->symbolInformation());

        return OpenFileAction::fromPathAndOffset($result->path(), $result->offset());
    }
}

