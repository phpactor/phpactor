<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Editor\OpenFileAction;
use Phpactor\Rpc\Editor\Input\TextInput;
use Phpactor\Application\Logger\NullLogger;
use Phpactor\Application\ClassMover;
use Phpactor\Rpc\Editor\StackAction;
use Phpactor\Rpc\Editor\EchoAction;
use Phpactor\Rpc\Editor\CloseFileAction;
use Phpactor\Rpc\Editor\Input\ConfirmInput;

class ClassMoveHandler extends AbstractHandler
{
    /**
     * @var ClassMover
     */
    private $classMove;

    /**
     * @var string
     */
    private $defaultFilesystem;

    public function __construct(ClassMover $classMove, string $defaultFilesystem)
    {
        $this->classMove = $classMove;
        $this->defaultFilesystem = $defaultFilesystem;
    }

    public function name(): string
    {
        return 'move_class';
    }

    public function defaultParameters(): array
    {
        return [
            'source_path' => null,
            'dest_path' => null,
            'confirmed' => null,
        ];
    }

    public function handle(array $arguments)
    {
        if (false === $arguments['confirmed']) {
            return EchoAction::fromMessage('Cancelled');
        }

        $this->requireArgument('dest_path', TextInput::fromNameLabelAndDefault(
            'dest_path',
            'Move to: ',
            $arguments['source_path']
        ));

        if (null !== $arguments['dest_path'] && null === $arguments['confirmed']) {
            $this->requireArgument('confirmed', ConfirmInput::fromNameAndLabel(
                'confirmed',
                'WARNING: This command will move the class and update ALL references in the git tree.' . PHP_EOL .
                '         It is not guaranteed to succeed. COMMIT YOUR WORK FIRST!' . PHP_EOL .
                'Are you sure? :'
            ));
        }

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        $this->classMove->move(
            new NullLogger(),
            $this->defaultFilesystem,
            $arguments['source_path'],
            $arguments['dest_path']
        );

        return StackAction::fromActions([
            CloseFileAction::fromPath($arguments['source_path']),
            OpenFileAction::fromPath($arguments['dest_path'])
        ]);
    }
}
