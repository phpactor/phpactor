<?php

namespace Phpactor\Extension\ReferenceFinderRpc\Handler;

use Phpactor\Extension\ReferenceFinderRpc\LocationSelector;
use Phpactor\Extension\Rpc\Response;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Response\OpenFileResponse;
use Phpactor\ReferenceFinder\TypeLocator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Locations;
use Phpactor\TextDocument\TextDocumentBuilder;

class GotoTypeHandler implements Handler
{
    const NAME = 'goto_type';
    const PARAM_OFFSET = 'offset';
    const PARAM_SOURCE = 'source';
    const PARAM_PATH = 'path';
    const PARAM_LANGUAGE = 'language';
    const PARAM_TARGET = 'target';

    public function __construct(
        private TypeLocator $locator,
        private LocationSelector $locationSelector
    ) {
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
        $resolver->setEnums([
            self::PARAM_TARGET => OpenFileResponse::VALID_TARGETS,
        ]);
        $resolver->setTypes([
            self::PARAM_OFFSET => 'integer',
            self::PARAM_LANGUAGE => 'string',
            self::PARAM_TARGET => 'string',
        ]);
        $resolver->setDescriptions([
            self::PARAM_OFFSET => 'Number of character into the buffer',
            self::PARAM_SOURCE => 'Content of the current file',
            self::PARAM_PATH => 'Path of the current file',
            self::PARAM_LANGUAGE => 'Language of the current file',
            self::PARAM_TARGET => 'Where should the reference be opened',
        ]);
    }

    public function handle(array $arguments): Response
    {
        $document = TextDocumentBuilder::create($arguments[self::PARAM_SOURCE])
            ->uri($arguments[self::PARAM_PATH])
            ->language($arguments[self::PARAM_LANGUAGE])->build();

        $offset = ByteOffset::fromInt($arguments[self::PARAM_OFFSET]);

        $locations = [];
        foreach($this->locator->locateTypes($document, $offset) as $typeLocation) {
            $locations[] = $typeLocation->location();
        }
        $locationsObject = new Locations($locations);

        return $this->locationSelector->selectFileOpenResponse($locationsObject, $arguments[self::PARAM_TARGET]);
    }
}
