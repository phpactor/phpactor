<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Rpc\ActionRequest;
use Phpactor\Application\ClassCopy;
use Phpactor\Rpc\Editor\OpenFileAction;

class CopyFileHandler implements Handler
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
        return 'copy_file';
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
            return InputCallbackAction::fromInputsAndAction(
                [
                    TextInput::fromNameLabelAndDefault('dest_path', 'Copy to:', $arguments['source_path']),
                ],
                ActionRequest::fromNameAndParameters(
                    $this->name(),
                    [
                        'source_path' => $arguments['source_path'],
                        'dest_path' => '%dest_path%',
                    ]
                )
            );
        }

        $this->classCopy->copy(null, $arguments['source_path'], $arguments['dest_path']);

        return OpenFileAction::fromPath($arguments['dest_path']);
    }
}
