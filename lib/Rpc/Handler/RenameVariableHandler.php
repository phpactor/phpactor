<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\CodeTransform\Domain\Refactor\RenameVariable;
use Phpactor\Rpc\Response\ReplaceFileSourceResponse;
use Phpactor\Rpc\Response\Input\TextInput;
use Phpactor\Rpc\Response\Input\ChoiceInput;
use Phpactor\CodeTransform\Domain\SourceCode;

class RenameVariableHandler extends AbstractHandler
{
    const NAME = 'rename_variable';
    const PARAM_OFFSET = 'offset';
    const PARAM_SOURCE = 'source';
    const PARAM_NAME = 'name';
    const PARAM_NAME_SUGGESTION = 'name_suggestion';
    const PARAM_PATH = 'path';
    const INPUT_LABEL = 'New name: ';
    const PARAM_SCOPE = 'scope';

    /**
     * @var RenameVariable
     */
    private $renameVariable;

    public function __construct(RenameVariable $renameVariable)
    {
        $this->renameVariable = $renameVariable;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function defaultParameters(): array
    {
        return [
            self::PARAM_PATH => null,
            self::PARAM_SOURCE => null,
            self::PARAM_OFFSET => null,
            self::PARAM_NAME => null,
            self::PARAM_NAME_SUGGESTION => null,
            self::PARAM_SCOPE => null,
        ];
    }

    public function handle(array $arguments)
    {
        $this->requireArgument(self::PARAM_NAME, TextInput::fromNameLabelAndDefault(
            self::PARAM_NAME,
            self::INPUT_LABEL,
            $arguments[self::PARAM_NAME_SUGGESTION] ?: ''
        ));

        $this->requireArgument(self::PARAM_SCOPE, ChoiceInput::fromNameLabelChoices(
            self::PARAM_SCOPE,
            'Scope: ',
            [
                RenameVariable::SCOPE_FILE => RenameVariable::SCOPE_FILE,
                RenameVariable::SCOPE_LOCAL => RenameVariable::SCOPE_LOCAL,
            ]
        ));

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        $sourceCode = $this->renameVariable->renameVariable(
            SourceCode::fromStringAndPath(
                $arguments[self::PARAM_SOURCE],
                $arguments[self::PARAM_PATH]
            ),
            $arguments[self::PARAM_OFFSET],
            $arguments[self::PARAM_NAME],
            $arguments[self::PARAM_SCOPE]
        );

        return ReplaceFileSourceResponse::fromPathAndSource(
            $sourceCode->path(),
            (string) $sourceCode
        );
    }
}
