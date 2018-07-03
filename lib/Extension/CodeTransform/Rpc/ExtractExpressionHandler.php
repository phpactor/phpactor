<?php

namespace Phpactor\Extension\CodeTransform\Rpc;

use Phpactor\CodeTransform\Domain\Refactor\ExtractExpression;
use Phpactor\Container\Schema;
use Phpactor\Extension\Rpc\Response\Input\TextInput;
use Phpactor\Extension\Rpc\Response\ReplaceFileSourceResponse;
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

    /**
     * @var ExtractExpression
     */
    private $extractExpression;

    public function __construct(ExtractExpression $extractExpression)
    {
        $this->extractExpression = $extractExpression;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Schema $schema): void
    {
        $schema->setDefaults([
            self::PARAM_PATH => null,
            self::PARAM_SOURCE => null,
            self::PARAM_VARIABLE_NAME => null,
            self::PARAM_OFFSET_START => null,
            self::PARAM_OFFSET_END => null,
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

        $sourceCode = $this->extractExpression->extractExpression(
            SourceCode::fromString($arguments[self::PARAM_SOURCE]),
            $arguments[self::PARAM_OFFSET_START],
            $arguments[self::PARAM_OFFSET_END],
            $arguments[self::PARAM_VARIABLE_NAME]
        );

        return ReplaceFileSourceResponse::fromPathAndSource(
            $arguments[self::PARAM_PATH],
            (string) $sourceCode
        );
    }
}
