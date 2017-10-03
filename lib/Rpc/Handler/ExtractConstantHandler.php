<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Rpc\Editor\EchoAction;
use Phpactor\CodeTransform\Domain\Refactor\ExtractConstant;
use Phpactor\Rpc\Editor\InputCallbackAction;
use Phpactor\Rpc\ActionRequest;
use Phpactor\Rpc\Editor\Input\TextInput;
use Phpactor\Rpc\Editor\ReplaceFileSourceAction;
use Phpactor\Rpc\Handler\AbstractHandler;

class ExtractConstantHandler extends AbstractHandler
{
    const NAME = 'extract_constant';

    /**
     * @var ExtractConstant
     */
    private $extractConstant;

    public function __construct(ExtractConstant $extractConstant)
    {
        $this->extractConstant = $extractConstant;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function defaultParameters(): array
    {
        return [
            'path' => null,
            'source' => null,
            'offset' => null,
            'constant_name' => null,
            'constant_name_suggestion' => null,
        ];
    }

    public function handle(array $arguments)
    {
        $this->requireArgument('constant_name', TextInput::fromNameLabelAndDefault(
            'constant_name',
            'Constant name',
            $arguments['constant_name_suggestion']
        ));

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        $sourceCode = $this->extractConstant->extractConstant(
            $arguments['source'],
            $arguments['offset'],
            $arguments['constant_name']
        );

        return ReplaceFileSourceAction::fromPathAndSource($arguments['path'], (string) $sourceCode);
    }
}

