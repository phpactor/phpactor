<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Application\ClassNew;
use Phpactor\Rpc\Editor\Input\TextInput;
use Phpactor\Rpc\Editor\Input\ChoiceInput;
use Phpactor\Rpc\Editor\StackAction;
use Phpactor\Rpc\Editor\InputCallbackAction;
use Phpactor\Rpc\ActionRequest;
use Phpactor\Application\Exception\FileAlreadyExists;
use Phpactor\Rpc\Editor\OpenFileAction;
use Phpactor\Rpc\Editor\EchoAction;
use Phpactor\Rpc\Editor\Input\ConfirmInput;

class ClassNewHandler implements Handler
{
    /**
     * @var ClassNew
     */
    private $classNew;

    public function __construct(ClassNew $classNew)
    {
        $this->classNew = $classNew;
    }

    public function name(): string
    {
        return 'class_new';
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

    public function handle(array $arguments)
    {
        if (false === $arguments['overwrite']) {
            return EchoAction::fromMessage('Cancelled');
        }

        $missingInputs = [];
        if (null === $arguments['new_path']) {
            $missingInputs[] = TextInput::fromNameLabelAndDefault(
                'new_path', 'Create at: ', $arguments['current_path']
            );
        }

        if (null === $arguments['variant']) {
            $missingInputs[] = ChoiceInput::fromNameLabelChoicesAndDefault(
                'variant', 
                'Variant: ', 
                array_combine(
                    $this->classNew->availableGenerators(),
                    $this->classNew->availableGenerators()
                )
            );
        }

        if ($missingInputs) {
            return InputCallbackAction::fromCallbackAndInputs(
                ActionRequest::fromNameAndParameters(
                    'class_new', [
                        'current_path' => $arguments['current_path'],
                        'new_path' => null,
                        'variant' => null,
                    ]
                ),
                $missingInputs
            );
        }

        try {
            $newPath = $this->classNew->generate($arguments['new_path'], $arguments['variant'], (bool) $arguments['overwrite']);
        } catch (FileAlreadyExists $e) {
            return InputCallbackAction::fromCallbackAndInputs(
                ActionRequest::fromNameAndParameters(
                    'class_new', [
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

