<?php

namespace Phpactor\Extension\CodeTransformExtra\Rpc;

use Phpactor\CodeTransform\Domain\Refactor\ExtractConstant;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\Rpc\Handler\AbstractHandler;
use Phpactor\Extension\Rpc\Response\Input\TextInput;
use Phpactor\Extension\Rpc\Response\UpdateFileSourceResponse;
use Phpactor\MapResolver\Resolver;

class ExtractConstantHandler extends AbstractHandler
{
    public const NAME = 'extract_constant';
    public const PARAM_CONSTANT_NAME = 'constant_name';
    public const PARAM_OFFSET = 'offset';
    public const PARAM_SOURCE = 'source';
    public const PARAM_PATH = 'path';
    public const PARAM_CONSTANT_NAME_SUGGESTION = 'constant_name_suggestion';
    public const INPUT_LABEL_NAME = 'Constant name: ';

    private ExtractConstant $extractConstant;

    public function __construct(ExtractConstant $extractConstant)
    {
        $this->extractConstant = $extractConstant;
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
