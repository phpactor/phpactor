<?php

namespace Phpactor\Extension\CodeTransform\Rpc;

use Phpactor\CodeTransform\Domain\Refactor\GenerateMethod;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Response\ReplaceFileSourceResponse;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\Rpc\Handler\AbstractHandler;

class GenerateMethodHandler extends AbstractHandler
{
    const NAME = 'generate_method';
    const PARAM_OFFSET = 'offset';
    const PARAM_SOURCE = 'source';
    const PARAM_PATH = 'path';

    /**
     * @var GenerateMethod
     */
    private $generateMethod;

    public function __construct(GenerateMethod $generateMethod)
    {
        $this->generateMethod = $generateMethod;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver)
    {
        $resolver->setRequired([
            self::PARAM_PATH,
            self::PARAM_SOURCE,
            self::PARAM_OFFSET,
        ]);
    }

    public function handle(array $arguments)
    {
        $sourceCode = $this->generateMethod->__invoke(
            SourceCode::fromStringAndPath(
                $arguments[self::PARAM_SOURCE],
                $arguments[self::PARAM_PATH]
            ),
            $arguments[self::PARAM_OFFSET]
        );

        return ReplaceFileSourceResponse::fromPathAndSource(
            $sourceCode->path(),
            (string) $sourceCode
        );
    }
}
