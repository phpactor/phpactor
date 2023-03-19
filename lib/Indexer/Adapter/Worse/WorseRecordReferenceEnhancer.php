<?php

namespace Phpactor\Indexer\Adapter\Worse;

use Phpactor\Indexer\Model\RecordReference;
use Phpactor\Indexer\Model\RecordReferenceEnhancer;
use Phpactor\Indexer\Model\Record\FileRecord;
use Phpactor\Indexer\Model\Record\MemberRecord;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Psr\Log\LoggerInterface;
use Safe\Exceptions\FilesystemException;
use function Safe\file_get_contents;

class WorseRecordReferenceEnhancer implements RecordReferenceEnhancer
{
    public function __construct(private SourceCodeReflector $reflector, private LoggerInterface $logger)
    {
    }

    public function enhance(FileRecord $record, RecordReference $reference): RecordReference
    {
        if ($reference->type() !== MemberRecord::RECORD_TYPE) {
            return $reference;
        }

        if ($reference->contaninerType()) {
            return $reference;
        }

        try {
            // TODO: We should get the latest in-memory source, e.g. from the
            // LS workspace. Perhaps add an adapter.
            $contents = file_get_contents($record->filePath());
        } catch (FilesystemException $error) {
            $this->logger->warning(sprintf(
                'Record Enhancer: Could not read file "%s": %s',
                $record->filePath(),
                $error->getMessage()
            ));
            return $reference;
        }

        try {
            $offset = $this->reflector->reflectOffset($contents, $reference->offset());
        } catch (NotFound $notFound) {
            $this->logger->debug(sprintf(
                'Record Enhancer: Could not reflect offset %s in file "%s": %s',
                $reference->offset(),
                $record->filePath(),
                $notFound->getMessage()
            ));
            return $reference;
        }

        $containerType = $offset->nodeContext()->containerType();

        if (!($containerType->isDefined())) {
            return $reference;
        }

        if ($containerType instanceof ClassType) {
            $containerType = $containerType->name()->__toString();
        }

        return $reference->withContainerType($containerType);
    }
}
