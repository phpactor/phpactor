<?php

namespace Phpactor\Extension\CodeTransformExtra\Rpc;

use Phpactor\CodeTransform\Domain\Refactor\ExtractExpression;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Response\Input\TextInput;
use Phpactor\Extension\Rpc\Response\UpdateFileSourceResponse;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\Rpc\Handler\AbstractHandler;

class ExtractExpressionHandler extends AbstractHandler
{
    const NAME = 'extract_expression';
    const PARAM_SOURCE = 'source';
    const PARAM_PATH = 'path';
    const PARAM_VARIABLE_NAME = 'variable_name';
    const PARAM_OFFSET_START = 'offset_start';
    const PARAM_OFFSET_END = 'offset_end';
    const INPUT_LABEL_NAME = 'Variable name: ';

    public function __construct(private readonly ExtractExpression $extractExpression)
    {
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_VARIABLE_NAME => null,
            self::PARAM_OFFSET_START => null,
        ]);
        $resolver->setRequired([
            self::PARAM_PATH,
            self::PARAM_SOURCE,
            self::PARAM_OFFSET_END,
        ]);
    }

    public function handle(array $arguments)
    {
        $this->requireInput(TextInput::fromNameLabelAndDefault(
            self::PARAM_VARIABLE_NAME,
            self::INPUT_LABEL_NAME,
            ''
        ));

        $this->requireInput(TextInput::fromNameLabelAndDefault(
            self::PARAM_OFFSET_START,
            'Offset start: '
        ));

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        $textEdits = $this->extractExpression->extractExpression(
            SourceCode::fromString($arguments[self::PARAM_SOURCE]),
            $arguments[self::PARAM_OFFSET_START],
            $arguments[self::PARAM_OFFSET_END],
            $arguments[self::PARAM_VARIABLE_NAME]
        );

        return UpdateFileSourceResponse::fromPathOldAndNewSource(
            $arguments[self::PARAM_PATH],
            $arguments[self::PARAM_SOURCE],
            $textEdits->apply($arguments[self::PARAM_SOURCE])
        );
    }
}
