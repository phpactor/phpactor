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
    /**
     * @var CodeTransform
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

    public function configure(Resolver $resolver)
    {
        $resolver->setDefaults([
            'transform' => null,
        ]);
        $resolver->setRequired([
            'path',
            'source',
        ]);
    }

    public function handle(array $arguments)
    {
        if (null === $arguments['transform']) {
            return $this->transformerChoiceAction($arguments['path'], $arguments['source']);
        }

        $code = SourceCode::fromStringAndPath($arguments['source'], $arguments['path']);

        $transformedCode = $this->codeTransform->transform($code, [
            $arguments['transform']
        ]);

        return UpdateFileSourceResponse::fromPathOldAndNewSource(
            $arguments['path'],
            $arguments['source'],
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
                    'transform' => null,
                    'path' => $path,
                    'source' => $source,
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
