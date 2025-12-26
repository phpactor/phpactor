<?php

namespace Phpactor\Extension\Navigation\Handler;

use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Navigation\Application\Navigator;
use RuntimeException;
use Phpactor\Extension\Rpc\Response\Input\ChoiceInput;
use Phpactor\Extension\Rpc\Response\Input\ConfirmInput;
use Phpactor\Extension\Rpc\Response\OpenFileResponse;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Extension\Rpc\Handler\AbstractHandler;

class NavigateHandler extends AbstractHandler
{
    const NAME = 'navigate';
    const PARAM_SOURCE_PATH = 'source_path';
    const PARAM_DESTINATION = 'destination';
    const PARAM_CONFIRM_CREATE = 'confirm_create';

    public function __construct(private readonly Navigator $navigator)
    {
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_SOURCE_PATH => null,
            self::PARAM_DESTINATION => null,
            self::PARAM_CONFIRM_CREATE => null,
        ]);
    }

    public function handle(array $arguments)
    {
        if (null === $arguments[self::PARAM_SOURCE_PATH]) {
            throw new RuntimeException(sprintf(
                'Param %s is required',
                self::PARAM_SOURCE_PATH
            ));
        }

        if (false === $arguments[self::PARAM_CONFIRM_CREATE]) {
            return EchoResponse::fromMessage('Cancelled');
        }

        $destinations = $this->navigator->destinationsFor($arguments[self::PARAM_SOURCE_PATH]);
        $this->requireInput(ChoiceInput::fromNameLabelChoices(
            self::PARAM_DESTINATION,
            'Destination:',
            array_combine(array_keys($destinations), array_keys($destinations))
        ));

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        $path = $destinations[$arguments[self::PARAM_DESTINATION]];
        $canCreate = $this->navigator->canCreateNew($arguments[self::PARAM_SOURCE_PATH], $arguments[self::PARAM_DESTINATION]);

        if ($canCreate) {
            $this->requireInput(ConfirmInput::fromNameAndLabel(
                self::PARAM_CONFIRM_CREATE,
                sprintf(
                    'File "%s" does not exist, generate new?: ',
                    $path
                )
            ));
        }

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        if ($canCreate) {
            $this->navigator->createNew($arguments[self::PARAM_SOURCE_PATH], $arguments[self::PARAM_DESTINATION]);
        }

        return OpenFileResponse::fromPath($path);
    }
}
