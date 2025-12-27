<?php

namespace Phpactor\Extension\CodeTransformExtra\Rpc;

use Phpactor\CodeTransform\Domain\Refactor\ExtractMethod;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Response\Input\TextInput;
use Phpactor\Extension\Rpc\Response\UpdateFileSourceResponse;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\Rpc\Handler\AbstractHandler;

class ExtractMethodHandler extends AbstractHandler
{
    const NAME = 'extract_method';
    const PARAM_SOURCE = 'source';
    const PARAM_PATH = 'path';
    const PARAM_METHOD_NAME = 'method_name';
    const PARAM_OFFSET_START = 'offset_start';
    const PARAM_OFFSET_END = 'offset_end';
    const INPUT_LABEL_NAME = 'Method name: ';

    public function __construct(private readonly ExtractMethod $extractMethod)
    {
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_METHOD_NAME => null,
            self::PARAM_OFFSET_START => null,
            self::PARAM_OFFSET_END => null,
        ]);
        $resolver->setRequired([
            self::PARAM_SOURCE,
            self::PARAM_PATH,
        ]);
    }

    public function handle(array $arguments)
    {
        $this->requireInput(TextInput::fromNameLabelAndDefault(
            self::PARAM_METHOD_NAME,
            self::INPUT_LABEL_NAME,
            ''
        ));

        $this->requireInput(TextInput::fromNameLabelAndDefault(
            self::PARAM_OFFSET_START,
            'Offset start: '
        ));

        $this->requireInput(TextInput::fromNameLabelAndDefault(
            self::PARAM_OFFSET_END,
            'Offset end: '
        ));

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        $sourceCode = SourceCode::fromStringAndPath($arguments[self::PARAM_SOURCE], $arguments[self::PARAM_PATH]);
        $textDocumentEdits = $this->extractMethod->extractMethod(
            $sourceCode,
            $arguments[self::PARAM_OFFSET_START],
            $arguments[self::PARAM_OFFSET_END],
            $arguments[self::PARAM_METHOD_NAME]
        );

        return UpdateFileSourceResponse::fromPathOldAndNewSource(
            $arguments[self::PARAM_PATH],
            $arguments[self::PARAM_SOURCE],
            $textDocumentEdits->textEdits()->apply((string)$sourceCode)
        );
    }
}
