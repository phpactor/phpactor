<?php

namespace Phpactor\Extension\CodeTransformExtra\Rpc;

use Phpactor\CodeTransform\Domain\Refactor\ExtractMethod;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\Rpc\Handler\AbstractHandler;
use Phpactor\Extension\Rpc\Response\Input\TextInput;
use Phpactor\Extension\Rpc\Response\UpdateFileSourceResponse;
use Phpactor\MapResolver\Resolver;

class ExtractMethodHandler extends AbstractHandler
{
    public const NAME = 'extract_method';
    public const PARAM_SOURCE = 'source';
    public const PARAM_PATH = 'path';
    public const PARAM_METHOD_NAME = 'method_name';
    public const PARAM_OFFSET_START = 'offset_start';
    public const PARAM_OFFSET_END = 'offset_end';
    public const INPUT_LABEL_NAME = 'Method name: ';

    private ExtractMethod $extractMethod;

    public function __construct(ExtractMethod $extractMethod)
    {
        $this->extractMethod = $extractMethod;
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
