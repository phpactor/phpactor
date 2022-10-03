<?php

namespace Phpactor\Extension\ReferenceFinderRpc\Handler;

use Phpactor\Extension\Rpc\Handler\AbstractHandler;
use Phpactor\Extension\Rpc\Response\FileReferencesResponse;
use Phpactor\Extension\Rpc\Response\OpenFileResponse;
use Phpactor\Extension\Rpc\Response\Reference\FileReferences;
use Phpactor\Extension\Rpc\Response\Reference\Reference;
use Phpactor\MapResolver\Resolver;
use Phpactor\ReferenceFinder\ClassImplementationFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\Locations;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\Util\LineAtOffset;
use Phpactor\TextDocument\Util\LineColFromOffset;
use RuntimeException;

class GotoImplementationHandler extends AbstractHandler
{
    public const NAME = 'goto_implementation';
    public const PARAM_OFFSET = 'offset';
    public const PARAM_SOURCE = 'source';
    public const PARAM_PATH = 'path';
    public const PARAM_LANGUAGE = 'language';
    public const PARAM_TARGET = 'target';
    public const PARAM_SELECTED_PATH = 'selected_path';

    private ClassImplementationFinder $finder;

    public function __construct(
        ClassImplementationFinder $finder
    ) {
        $this->finder = $finder;
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
            $lineCol = (new LineColFromOffset())($contents, $location->offset()->toInt());
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
