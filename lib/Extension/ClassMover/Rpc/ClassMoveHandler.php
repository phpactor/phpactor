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
    const PARAM_SOURCE_PATH = 'source_path';
    const PARAM_DEST_PATH = 'dest_path';
    const PARAM_CONFIRMED = 'confirmed';

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

    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_SOURCE_PATH => null,
            self::PARAM_DEST_PATH => null,
            self::PARAM_CONFIRMED => null,
        ]);
        $schema->setRequired([
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

        if (null !== $arguments[self::PARAM_DEST_PATH] && null === $arguments[self::PARAM_CONFIRMED]) {
            $this->requireInput(ConfirmInput::fromNameAndLabel(
                self::PARAM_CONFIRMED,
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
            $arguments[self::PARAM_SOURCE_PATH],
            $arguments[self::PARAM_DEST_PATH]
        );

        return CollectionResponse::fromActions([
            CloseFileResponse::fromPath($arguments[self::PARAM_SOURCE_PATH]),
            OpenFileResponse::fromPath($arguments[self::PARAM_DEST_PATH])
        ]);
    }
}
