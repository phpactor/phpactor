<?php

namespace Phpactor\Extension\CodeTransformExtra\Rpc;

use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Response\Input\TextInput;
use Phpactor\Extension\Rpc\Response\Input\ChoiceInput;
use Phpactor\Extension\Rpc\Response\InputCallbackResponse;
use Phpactor\Extension\Rpc\Request;
use Phpactor\Extension\CodeTransformExtra\Application\Exception\FileAlreadyExists;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Extension\Rpc\Response\Input\ConfirmInput;
use Phpactor\Extension\CodeTransformExtra\Application\AbstractClassGenerator;
use Phpactor\Extension\Rpc\Handler\AbstractHandler;
use Phpactor\Extension\Rpc\Response\ReplaceFileSourceResponse;

abstract class AbstractClassGenerateHandler extends AbstractHandler
{
    const PARAM_CURRENT_PATH = 'current_path';
    const PARAM_NEW_PATH = 'new_path';
    const PARAM_VARIANT = 'variant';
    const PARAM_OVERWRITE = 'overwrite';

    public function __construct(protected AbstractClassGenerator $classGenerator)
    {
    }

    public function configure(Resolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_NEW_PATH => null,
            self::PARAM_VARIANT => null,
            self::PARAM_OVERWRITE => null,
        ]);
        $resolver->setRequired([
            self::PARAM_CURRENT_PATH
        ]);
    }

    public function handle(array $arguments)
    {
        if (false === $arguments[self::PARAM_OVERWRITE]) {
            return EchoResponse::fromMessage('Cancelled');
        }

        $missingInputs = [];

        if (null === $arguments[self::PARAM_VARIANT]) {
            $this->requireInput(ChoiceInput::fromNameLabelChoicesAndDefault(
                self::PARAM_VARIANT,
                'Variant: ',
                array_combine(
                    $this->classGenerator->availableGenerators(),
                    $this->classGenerator->availableGenerators()
                )
            ));
        }

        $this->requireInput(TextInput::fromNameLabelAndDefault(
            self::PARAM_NEW_PATH,
            $this->newMessage(),
            $arguments[self::PARAM_CURRENT_PATH],
            'file'
        ));

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        try {
            $code = $this->generate($arguments);
        } catch (FileAlreadyExists) {
            return InputCallbackResponse::fromCallbackAndInputs(
                Request::fromNameAndParameters(
                    $this->name(),
                    [
                        self::PARAM_CURRENT_PATH => $arguments[self::PARAM_CURRENT_PATH],
                        self::PARAM_NEW_PATH => $arguments[self::PARAM_NEW_PATH],
                        self::PARAM_VARIANT => $arguments[self::PARAM_VARIANT],
                        self::PARAM_OVERWRITE => null,
                    ]
                ),
                [
                    ConfirmInput::fromNameAndLabel(
                        self::PARAM_OVERWRITE,
                        'File already exists, overwrite? :'
                    )
                ]
            );
        }

        return ReplaceFileSourceResponse::fromPathAndSource($code->uri()?->path(), (string) $code);
    }

    abstract protected function generate(array $arguments): SourceCode;

    abstract protected function newMessage(): string;
}
