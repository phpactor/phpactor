<?php

namespace Phpactor\Extension\Rpc\Handler;

use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Response\Input\Input;
use Phpactor\Extension\Rpc\Response\InputCallbackResponse;
use Phpactor\Extension\Rpc\Request;
use InvalidArgumentException;

abstract class AbstractHandler implements Handler
{
    /** @var array<string, Input> */
    private array $requiredArguments = [];

    protected function requireInput(Input $input): void
    {
        $this->requiredArguments[$input->name()] = $input;
    }

    /** @param array<mixed> $arguments */
    protected function hasMissingArguments(array $arguments): bool
    {
        if (count($this->missingArguments($arguments)) > 0) {
            return true;
        }

        return false;
    }

    /** @param array<mixed> $arguments */
    protected function createInputCallback(array $arguments): InputCallbackResponse
    {
        return InputCallbackResponse::fromCallbackAndInputs(
            Request::fromNameAndParameters(
                $this->name(),
                $arguments
            ),
            $this->inputsFromMissingArguments($arguments)
        );
    }

    /**
     * @param array<mixed> $arguments
     *
     * @return array<array-key>
     */
    private function missingArguments(array $arguments): array
    {
        return array_keys(array_filter($arguments, function (mixed $argument, string|int $key) {
            if (false === isset($this->requiredArguments[$key])) {
                return false;
            }

            return empty($argument);
        }, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * @param array<mixed> $arguments
     *
     * @return array<int, mixed>
     */
    private function inputsFromMissingArguments(array $arguments): array
    {
        $inputs = [];
        foreach ($this->missingArguments($arguments) as $argumentName) {
            if (false === isset($this->requiredArguments[$argumentName])) {
                throw new InvalidArgumentException(sprintf(
                    'Parameter "%s" is not set and no interactive input was made available for it',
                    $argumentName
                ));
            }

            $inputs[] = $this->requiredArguments[$argumentName];
        }

        return array_reverse($inputs);
    }
}
