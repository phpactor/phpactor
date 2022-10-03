<?php

namespace Phpactor\Extension\CodeTransformExtra\Rpc;

use Phpactor\CodeTransform\Domain\Refactor\RenameVariable;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\Rpc\Handler\AbstractHandler;
use Phpactor\Extension\Rpc\Response\Input\ChoiceInput;
use Phpactor\Extension\Rpc\Response\Input\TextInput;
use Phpactor\Extension\Rpc\Response\UpdateFileSourceResponse;
use Phpactor\MapResolver\Resolver;

class RenameVariableHandler extends AbstractHandler
{
    public const NAME = 'rename_variable';
    public const PARAM_OFFSET = 'offset';
    public const PARAM_SOURCE = 'source';
    public const PARAM_NAME = 'name';
    public const PARAM_NAME_SUGGESTION = 'name_suggestion';
    public const PARAM_PATH = 'path';
    public const INPUT_LABEL = 'New name: ';
    public const PARAM_SCOPE = 'scope';

    private RenameVariable $renameVariable;

    public function __construct(RenameVariable $renameVariable)
    {
        $this->renameVariable = $renameVariable;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_NAME => null,
            self::PARAM_NAME_SUGGESTION => null,
            self::PARAM_SCOPE => null,
        ]);
        $resolver->setRequired([
            self::PARAM_PATH,
            self::PARAM_SOURCE,
            self::PARAM_OFFSET,
        ]);
    }

    public function handle(array $arguments)
    {
        $this->requireInput(TextInput::fromNameLabelAndDefault(
            self::PARAM_NAME,
            self::INPUT_LABEL,
            $arguments[self::PARAM_NAME_SUGGESTION] ?: ''
        ));

        $this->requireInput(ChoiceInput::fromNameLabelChoices(
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

        return UpdateFileSourceResponse::fromPathOldAndNewSource(
            $sourceCode->path(),
            $arguments[self::PARAM_SOURCE],
            (string) $sourceCode
        );
    }
}
