<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\CodeTransform\Domain\Refactor\GenerateAccessor;
use Phpactor\Rpc\Editor\ReplaceFileSourceAction;

class GenerateAccessorHandler extends AbstractHandler
{
    const NAME = 'generate_accessor';
    const PARAM_OFFSET = 'offset';
    const PARAM_SOURCE = 'source';
    const PARAM_PATH = 'path';

    /**
     * @var GenerateAccessor
     */
    private $generateAccessor;

    public function __construct(
        GenerateAccessor $generateAccessor
    ) {
        $this->generateAccessor = $generateAccessor;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function defaultParameters(): array
    {
        return [
            self::PARAM_PATH => null,
            self::PARAM_SOURCE => null,
            self::PARAM_OFFSET => null,
        ];
    }

    public function handle(array $arguments)
    {
        $sourceCode = $this->generateAccessor->generateAccessor(
            $arguments[self::PARAM_SOURCE],
            $arguments[self::PARAM_OFFSET]
        );

        return ReplaceFileSourceAction::fromPathAndSource(
            $sourceCode->path() ?: $arguments[self::PARAM_PATH],
            (string) $sourceCode
        );
    }
}
