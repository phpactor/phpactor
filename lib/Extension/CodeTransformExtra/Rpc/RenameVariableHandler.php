<?php

namespace Phpactor\Extension\CodeTransformExtra\Rpc;

use Phpactor\CodeTransform\Domain\Refactor\RenameVariable;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Response\UpdateFileSourceResponse;
use Phpactor\Extension\Rpc\Response\Input\TextInput;
use Phpactor\Extension\Rpc\Response\Input\ChoiceInput;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\Rpc\Handler\AbstractHandler;

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

    public function __construct(private readonly RenameVariable $renameVariable)
    {
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
            $sourceCode->uri()->path(),
            $arguments[self::PARAM_SOURCE],
            (string) $sourceCode
        );
    }
}
