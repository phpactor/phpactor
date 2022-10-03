<?php

namespace Phpactor\Extension\ReferenceFinderRpc\Handler;

use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Response\OpenFileResponse;
use Phpactor\MapResolver\Resolver;
use Phpactor\ReferenceFinder\TypeLocator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;

class GotoTypeHandler implements Handler
{
    public const NAME = 'goto_type';
    public const PARAM_OFFSET = 'offset';
    public const PARAM_SOURCE = 'source';
    public const PARAM_PATH = 'path';
    public const PARAM_LANGUAGE = 'language';
    public const PARAM_TARGET = 'target';

    private TypeLocator $locator;

    public function __construct(
        TypeLocator $locator
    ) {
        $this->locator = $locator;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_LANGUAGE => 'php',
            self::PARAM_TARGET => OpenFileResponse::TARGET_FOCUSED_WINDOW
        ]);
        $resolver->setRequired([
            self::PARAM_OFFSET,
            self::PARAM_SOURCE,
            self::PARAM_PATH,
        ]);
    }

    public function handle(array $arguments)
    {
        $document = TextDocumentBuilder::create($arguments[self::PARAM_SOURCE])
            ->uri($arguments[self::PARAM_PATH])
            ->language($arguments[self::PARAM_LANGUAGE])->build();

        $offset = ByteOffset::fromInt($arguments[self::PARAM_OFFSET]);
        $location = $this->locator->locateTypes($document, $offset)->first();

        return OpenFileResponse::fromPathAndOffset(
            $location->location()->uri()->path(),
            $location->location()->offset()->toInt()
        )->withTarget($arguments[self::PARAM_TARGET]);
    }
}
