<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Rpc\ActionRequest;
use Phpactor\Application\ClassCopy;
use Phpactor\Rpc\Editor\OpenFileAction;
use Phpactor\Rpc\Editor\InputCallbackAction;
use Phpactor\Rpc\Editor\Input\TextInput;
use Phpactor\Application\Logger\NullLogger;

class ClassCopyHandler implements Handler
{
    /**
     * @var ClassCopy
     */
    private $classCopy;

    public function __construct(ClassCopy $classCopy)
    {
        $this->classCopy = $classCopy;
    }

    public function name(): string
    {
        return 'copy_class';
    }

    public function defaultParameters(): array
    {
        return [
            'source_path' => null,
            'dest_path' => null,
        ];
    }

    public function handle(array $arguments)
    {
        if (null === $arguments['dest_path']) {

            // get destination path
            return InputCallbackAction::fromCallbackAndInputs(
                ActionRequest::fromNameAndParameters(
                    $this->name(),
                    [
                        'source_path' => $arguments['source_path'],
                        'dest_path' => null,
                    ]
                ),
                [
                    TextInput::fromNameLabelAndDefault('dest_path', 'Copy to: ', $arguments['source_path']),
                ]
            );
        }

        $this->classCopy->copy(new NullLogger(), $arguments['source_path'], $arguments['dest_path']);

        return OpenFileAction::fromPath($arguments['dest_path']);
    }
}
