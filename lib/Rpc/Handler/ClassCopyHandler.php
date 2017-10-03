<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Application\ClassCopy;
use Phpactor\Rpc\Editor\OpenFileAction;
use Phpactor\Rpc\Editor\Input\TextInput;
use Phpactor\Application\Logger\NullLogger;

class ClassCopyHandler extends AbstractHandler
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
        $this->requireArgument('dest_path', TextInput::fromNameLabelAndDefault('dest_path', 'Copy to: ', $arguments['source_path']));

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        $this->classCopy->copy(new NullLogger(), $arguments['source_path'], $arguments['dest_path']);

        return OpenFileAction::fromPath($arguments['dest_path']);
    }
}
