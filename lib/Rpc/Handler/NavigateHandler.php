<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler\AbstractHandler;
use Phpactor\Application\Navigator;
use Phpactor\Rpc\Response\ErrorResponse;
use RuntimeException;
use Phpactor\Rpc\Response\Input\ChoiceInput;
use Phpactor\Rpc\Response\Input\ConfirmInput;
use Phpactor\Rpc\Response\CollectionResponse;
use Phpactor\Rpc\Response\OpenFileResponse;

class NavigateHandler extends AbstractHandler
{
    const NAME = 'navigate';
    const PARAM_SOURCE_PATH = 'source_path';
    const PARAM_DESTINATION = 'destination';
    const PARAM_CONFIRM_CREATE = 'confirm_create';


    /**
     * @var Navigator
     */
    private $navigator;

    public function __construct(Navigator $navigator)
    {
        $this->navigator = $navigator;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function defaultParameters(): array
    {
        return [
            self::PARAM_SOURCE_PATH => null,
            self::PARAM_DESTINATION => null,
            self::PARAM_CONFIRM_CREATE => null,
        ];
    }

    public function handle(array $arguments)
    {
        if (null === $arguments[self::PARAM_SOURCE_PATH]) {
            throw new RuntimeException(sprtinf(
                'Param %s is required', self::PARAM_SOURCE_PATH
            ));
        }

        $destinations = $this->navigator->destinationsFor($arguments[self::PARAM_SOURCE_PATH]);
        $this->requireArgument(self::PARAM_DESTINATION, ChoiceInput::fromNameLabelChoices(
            self::PARAM_DESTINATION,
            self::PARAM_DESTINATION,
            array_combine(array_keys($destinations), array_keys($destinations))
        ));

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        if ($this->navigator->canCreateNew($arguments[self::PARAM_SOURCE_PATH], $arguments[self::PARAM_DESTINATION])) {
            $this->requireArgument(self::PARAM_CONFIRM_CREATE, ConfirmInput::fromNameAndLabel(
                self::PARAM_CONFIRM_CREATE,
                'This file does not exist, create new? :'
            ));
        }

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        $path = $destinations[$arguments[self::PARAM_DESTINATION]];

        return OpenFileResponse::fromPath($path);
    }
}
