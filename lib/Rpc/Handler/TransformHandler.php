<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Application\Transformer;
use Phpactor\CodeTransform\CodeTransform;
use Phpactor\Rpc\Editor\Input\ChoiceInput;
use Phpactor\Rpc\Editor\InputCallbackAction;
use Phpactor\Rpc\ActionRequest;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Rpc\Editor\ReplaceFileSourceAction;

class TransformHandler implements Handler
{
    /**
     * @var Transformer
     */
    private $codeTransform;

    public function __construct(CodeTransform $codeTransform)
    {
        $this->codeTransform = $codeTransform;
    }

    public function name(): string
    {
        return 'transform';
    }

    public function defaultParameters(): array
    {
        return [
            'path' => null,
            'transform' => null,
            'source' => null,
        ];
    }

    public function handle(array $arguments)
    {
        if (null === $arguments['transform']) {
            return $this->transformerChoiceAction();
        }

        $code = SourceCode::fromString($arguments['source']);

        $transformedCode = $this->codeTransform->transform($code, [
            $arguments['transform']
        ]);

        return ReplaceFileSourceAction::fromPathAndSource($arguments['path'], (string) $transformedCode);
    }

    private function transformerChoiceAction()
    {
        $transformers= $this->codeTransform->transformers()->names();

        // get destination path
        return InputCallbackAction::fromCallbackAndInputs(
            ActionRequest::fromNameAndParameters(
                $this->name(),
                [
                    'transform' => null,
                ]
            ),
            [
                ChoiceInput::fromNameLabelChoicesAndDefault(
                    'transform',
                    'Transform: ',
                    array_combine($transformers, $transformers)
                )
            ]
        );
    }
}

