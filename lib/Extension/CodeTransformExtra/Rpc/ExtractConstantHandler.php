<?php

namespace Phpactor\Extension\CodeTransformExtra\Rpc;

use Phpactor\CodeTransform\Domain\Refactor\ExtractConstant;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Response\Input\TextInput;
use Phpactor\Extension\Rpc\Response\UpdateFileSourceResponse;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\Rpc\Handler\AbstractHandler;

class ExtractConstantHandler extends AbstractHandler
{
    const NAME = 'extract_constant';
    const PARAM_CONSTANT_NAME = 'constant_name';
    const PARAM_OFFSET = 'offset';
    const PARAM_SOURCE = 'source';
    const PARAM_PATH = 'path';
    const PARAM_CONSTANT_NAME_SUGGESTION = 'constant_name_suggestion';
    const INPUT_LABEL_NAME = 'Constant name: ';

    public function __construct(private readonly ExtractConstant $extractConstant)
    {
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_CONSTANT_NAME => null,
            self::PARAM_CONSTANT_NAME_SUGGESTION => null,
        ]);
        $resolver->setRequired([
            self::PARAM_PATH,
            self::PARAM_OFFSET,
            self::PARAM_SOURCE,
        ]);
    }

    public function handle(array $arguments)
    {
        $this->requireInput(TextInput::fromNameLabelAndDefault(
            self::PARAM_CONSTANT_NAME,
            self::INPUT_LABEL_NAME,
            $arguments[self::PARAM_CONSTANT_NAME_SUGGESTION] ?: ''
        ));

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        $textEdits = $this->extractConstant->extractConstant(
            SourceCode::fromStringAndPath($arguments[self::PARAM_SOURCE], $arguments[self::PARAM_PATH]),
            $arguments[self::PARAM_OFFSET],
            $arguments[self::PARAM_CONSTANT_NAME]
        );

        return UpdateFileSourceResponse::fromPathOldAndNewSource(
            $arguments[self::PARAM_PATH],
            $arguments[self::PARAM_SOURCE],
            $textEdits->textEdits()->apply($arguments[self::PARAM_SOURCE])
        );
    }
}
