<?php

namespace Phpactor\Extension\ClassMover\Rpc;

use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Response\OpenFileResponse;
use Phpactor\Extension\Rpc\Response\Input\TextInput;
use Phpactor\Extension\ClassMover\Application\Logger\NullLogger;
use Phpactor\Extension\ClassMover\Application\ClassMover;
use Phpactor\Extension\Rpc\Response\CollectionResponse;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Extension\Rpc\Response\CloseFileResponse;
use Phpactor\Extension\Rpc\Response\Input\ConfirmInput;
use Phpactor\Extension\Rpc\Handler\AbstractHandler;

class ClassMoveHandler extends AbstractHandler
{
    const NAME = 'move_class';
    private const PARAM_SOURCE_PATH = 'source_path';
    private const PARAM_DEST_PATH = 'dest_path';
    private const PARAM_CONFIRMED = 'confirmed';
    private const PARAM_ADDITIONAL_MOVE_CONFIRM = 'move_related';

    public function __construct(private ClassMover $classMove, private string $defaultFilesystem)
    {
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_DEST_PATH => null,
            self::PARAM_CONFIRMED => null,
            self::PARAM_ADDITIONAL_MOVE_CONFIRM => null,
        ]);
        $resolver->setRequired([
            self::PARAM_SOURCE_PATH
        ]);
    }

    public function handle(array $arguments)
    {
        if (false === $arguments[self::PARAM_CONFIRMED]) {
            return EchoResponse::fromMessage('Cancelled');
        }

        $this->requireInput(TextInput::fromNameLabelAndDefault(
            self::PARAM_DEST_PATH,
            'Move to: ',
            $arguments[self::PARAM_SOURCE_PATH],
            'file'
        ));

        if (
            null !== $arguments[self::PARAM_DEST_PATH] &&
            null === $arguments[self::PARAM_CONFIRMED]
        ) {
            $this->requireInput(ConfirmInput::fromNameAndLabel(
                self::PARAM_CONFIRMED,
                'WARNING: This command will move the class and update ALL references in the git tree.' . "\n" .
                '         It is not guaranteed to succeed. COMMIT YOUR WORK FIRST!' . "\n" .
                'Are you sure? :'
            ));
        }

        if (
            null === $arguments[self::PARAM_ADDITIONAL_MOVE_CONFIRM] &&
            $arguments[self::PARAM_DEST_PATH] &&
            $related = $this->classMove->getRelatedFiles($arguments[self::PARAM_SOURCE_PATH])
        ) {
            $this->requireInput(ConfirmInput::fromNameAndLabel(
                self::PARAM_ADDITIONAL_MOVE_CONFIRM,
                sprintf(
                    "This class has the following related files:\n\n - %s\n\nMove these too? ",
                    implode("\n - ", $related)
                )
            ));
        }

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        $this->classMove->move(
            new NullLogger(),
            $this->defaultFilesystem,
            $arguments[self::PARAM_SOURCE_PATH],
            $arguments[self::PARAM_DEST_PATH],
            $arguments[self::PARAM_ADDITIONAL_MOVE_CONFIRM] ?? false
        );

        return CollectionResponse::fromActions([
            OpenFileResponse::fromPath($arguments[self::PARAM_DEST_PATH]),
            CloseFileResponse::fromPath($arguments[self::PARAM_SOURCE_PATH])
        ]);
    }
}
