<?php

namespace Phpactor\Extension\ReferenceFinderRpc\Handler;

use Phpactor\Extension\Rpc\Handler\AbstractHandler;
use Phpactor\Extension\Rpc\Response\FileReferencesResponse;
use Phpactor\Extension\Rpc\Response\Reference\FileReferences;
use Phpactor\Extension\Rpc\Response\Reference\Reference;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Response\OpenFileResponse;
use Phpactor\ReferenceFinder\ClassImplementationFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\Locations;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\LineCol;
use Phpactor\TextDocument\Util\LineAtOffset;
use RuntimeException;

class GotoImplementationHandler extends AbstractHandler
{
    const NAME = 'goto_implementation';
    const PARAM_OFFSET = 'offset';
    const PARAM_SOURCE = 'source';
    const PARAM_PATH = 'path';
    const PARAM_LANGUAGE = 'language';
    const PARAM_TARGET = 'target';

    public function __construct(private ClassImplementationFinder $finder)
    {
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

    public function handle(array $arguments)
    {
        $document = TextDocumentBuilder::create($arguments[self::PARAM_SOURCE])
            ->uri($arguments[self::PARAM_PATH])
            ->language($arguments[self::PARAM_LANGUAGE])->build();

        $offset = ByteOffset::fromInt($arguments[self::PARAM_OFFSET]);
        $locations = $this->finder->findImplementations($document, $offset);

        if (1 !== $locations->count()) {
            $references = $this->locationsToReferences($locations);

            return new FileReferencesResponse($references);
        }

        $location = $locations->first();
        return OpenFileResponse::fromPathAndOffset(
            $location->uri()->path(),
            $location->offset()->toInt()
        )->withTarget($arguments[self::PARAM_TARGET]);
    }

    private function locationsToReferences(Locations $locations): array
    {
        $references = [];
        foreach ($locations as $location) {
            assert($location instanceof Location);
            $contents = $this->fileContents($location);
            $lineCol = LineCol::fromByteOffset($contents, $location->offset());
            $line = (new LineAtOffset())->__invoke($contents, $location->offset()->toInt());

            $fileReferences = FileReferences::fromPathAndReferences(
                $location->uri()->path(),
                [
                    Reference::fromStartEndLineNumberLineAndCol($location->offset()->toInt(), $location->offset()->toInt(), $lineCol->line(), $line, $lineCol->col())
                ]
            );
            $references[] = $fileReferences;
        }

        return $references;
    }

    private function fileContents(Location $location)
    {
        if (!file_exists($location->uri()->path())) {
            throw new RuntimeException(sprintf(
                'Could not open file "%s"',
                $location->uri()->path()
            ));
        }

        return file_get_contents($location->uri()->path());
    }
}
