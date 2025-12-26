<?php

namespace Phpactor\Extension\CodeTransform\Rpc;

use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\CodeTransform\CodeTransform;
use Phpactor\Extension\Rpc\Response\Input\ChoiceInput;
use Phpactor\Extension\Rpc\Response\InputCallbackResponse;
use Phpactor\Extension\Rpc\Request;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\Rpc\Response\UpdateFileSourceResponse;

class TransformHandler implements Handler
{
    const NAME = 'transform';
    const PARAM_NAME = 'transform';
    const PARAM_PATH = 'path';
    const PARAM_SOURCE = 'source';

    public function __construct(private readonly CodeTransform $codeTransform)
    {
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_NAME => null,
        ]);
        $resolver->setRequired([
            self::PARAM_PATH,
            self::PARAM_SOURCE,
        ]);
    }

    public function handle(array $arguments)
    {
        if (null === $arguments[self::PARAM_NAME]) {
            return $this->transformerChoiceAction($arguments[self::PARAM_PATH], $arguments[self::PARAM_SOURCE]);
        }

        $code = SourceCode::fromStringAndPath($arguments[self::PARAM_SOURCE], $arguments[self::PARAM_PATH]);

        $transformedCode = $this->codeTransform->transform($code, [
            $arguments[self::PARAM_NAME]
        ]);

        return UpdateFileSourceResponse::fromPathOldAndNewSource(
            $arguments[self::PARAM_PATH],
            $arguments[self::PARAM_SOURCE],
            (string) $transformedCode
        );
    }

    private function transformerChoiceAction(string $path, string $source)
    {
        $transformers= $this->codeTransform->transformers()->names();

        // get destination path
        return InputCallbackResponse::fromCallbackAndInputs(
            Request::fromNameAndParameters(
                $this->name(),
                [
                    self::PARAM_NAME => null,
                    self::PARAM_PATH => $path,
                    self::PARAM_SOURCE => $source,
                ]
            ),
            [
                ChoiceInput::fromNameLabelChoicesAndDefault(
                    self::PARAM_NAME,
                    'Transform: ',
                    (array) array_combine($transformers, $transformers)
                )
            ]
        );
    }
}
