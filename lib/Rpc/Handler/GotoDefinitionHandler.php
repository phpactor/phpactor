<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Rpc\Response\OpenFileResponse;
use Phpactor\Core\GotoDefinition\GotoDefinition;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Offset;

class GotoDefinitionHandler implements Handler
{
    const NAME = 'goto_definition';
    const PARAM_OFFSET = 'offset';
    const PARAM_SOURCE = 'source';

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
    ) {
        $this->reflector = $reflector;
        $this->gotoDefinition = new GotoDefinition($reflector);
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function defaultParameters(): array
    {
        return [
            self::PARAM_OFFSET => null,
            self::PARAM_SOURCE => null,
        ];
    }

    public function handle(array $arguments)
    {
        $result = $this->reflector->reflectOffset(
            SourceCode::fromString($arguments[self::PARAM_SOURCE]),
            Offset::fromInt($arguments[self::PARAM_OFFSET])
        );

        $result = $this->gotoDefinition->gotoDefinition($result->symbolContext());

        return OpenFileResponse::fromPathAndOffset($result->path(), $result->offset());
    }
}
