<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Application\ClassGenerator;
use Phpactor\Rpc\Editor\Input\TextInput;
use Phpactor\Rpc\Editor\Input\ChoiceInput;
use Phpactor\Rpc\Editor\InputCallbackAction;
use Phpactor\Rpc\ActionRequest;
use Phpactor\Application\Exception\FileAlreadyExists;
use Phpactor\Rpc\Editor\OpenFileAction;
use Phpactor\Rpc\Editor\EchoAction;
use Phpactor\Rpc\Editor\Input\ConfirmInput;
use Phpactor\Application\AbstractClassGenerator;

abstract class AbstractClassGenerateHandler implements Handler
{
    /**
     * @var ClassGenerator
     */
    protected $classGenerator;

    public function __construct(AbstractClassGenerator $classGenerator)
    {
        $this->classGenerator = $classGenerator;
    }

    public function defaultParameters(): array
    {
        return [
            'current_path' => null,
            'new_path' => null,
            'variant' => null,
            'overwrite' => null,
        ];
    }

    abstract protected function generate(array $arguments);

    abstract protected function newMessage(): string;

    public function handle(array $arguments)
    {
        if (false === $arguments['overwrite']) {
            return EchoAction::fromMessage('Cancelled');
        }

        $missingInputs = [];

        if (null === $arguments['variant']) {
            $missingInputs[] = ChoiceInput::fromNameLabelChoicesAndDefault(
                'variant',
                'Variant: ',
                array_combine(
                    $this->classGenerator->availableGenerators(),
                    $this->classGenerator->availableGenerators()
                )
            );
        }

        if (null === $arguments['new_path']) {
            $missingInputs[] = TextInput::fromNameLabelAndDefault(
                'new_path',
                $this->newMessage(),
                $arguments['current_path']
            );
        }

        if ($missingInputs) {
            return InputCallbackAction::fromCallbackAndInputs(
                ActionRequest::fromNameAndParameters(
                    $this->name(),
                    [
                        'current_path' => $arguments['current_path'],
                        'new_path' => null,
                        'variant' => null,
                    ]
                ),
                $missingInputs
            );
        }

        try {
            $newPath = $this->generate($arguments);
        } catch (FileAlreadyExists $e) {
            return InputCallbackAction::fromCallbackAndInputs(
                ActionRequest::fromNameAndParameters(
                    $this->name(),
                    [
                        'current_path' => $arguments['current_path'],
                        'new_path' => $arguments['new_path'],
                        'variant' => $arguments['variant'],
                        'overwrite' => null,
                    ]
                ),
                [
                    ConfirmInput::fromNameAndLabel(
                        'overwrite',
                        'File already exists, overwrite? :'
                    )
                ]
            );
        }

        return OpenFileAction::fromPath($newPath);
    }
}
