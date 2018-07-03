<?php

namespace Phpactor\Extension\CodeTransform\Rpc;

use Phpactor\CodeTransform\Domain\Refactor\GenerateMethod;
use Phpactor\Container\Schema;
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

    public function configure(Schema $schema): void
    {
        $schema->setDefaults([
            self::PARAM_PATH => null,
            self::PARAM_SOURCE => null,
            self::PARAM_OFFSET => null,
        ]);
    }

    public function handle(array $arguments)
    {
        $sourceCode = $this->generateMethod->generateMethod(
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
