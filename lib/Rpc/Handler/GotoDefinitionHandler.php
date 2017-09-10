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
     * @var GotoDefinition
     */
    private $gotoDefinition;

    public function __construct(
        Reflector $reflector
    )
    {
        $this->reflector = $reflector;
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
            'source' => null,
        ];
    }

    public function handle(array $arguments)
    {
        $result = $this->reflector->reflectOffset(
            SourceCode::fromString($arguments['source']),
            Offset::fromInt($arguments['offset'])
        );

        $result = $this->gotoDefinition->gotoDefinition($result->symbolInformation());

        return OpenFileAction::fromPathAndOffset($result->path(), $result->offset());
    }
}

