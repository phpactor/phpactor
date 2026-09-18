<?php

namespace Phpactor\Indexer\Model;

use Phpactor\Indexer\Model\Name\FullyQualifiedName;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\Record\ConstantRecord;
use Phpactor\Indexer\Model\Record\FileRecord;
use Phpactor\Indexer\Model\Record\FunctionRecord;
use Phpactor\Indexer\Model\Record\MemberRecord;
use Phpactor\TextDocument\TextDocumentUri;

/**
 * Merges a record into an index which may already hold a record with
 * the same identifier.
 */
final class RecordMerger
{
    public function merge(Index $index, Record $incoming): void
    {
        $existing = $index->get($incoming);

        if ($existing === $incoming) {
            $index->write($incoming);

            return;
        }

        $index->write(match (true) {
            $incoming instanceof FileRecord => $incoming,
            $incoming instanceof ClassRecord && $existing instanceof ClassRecord
                => $this->mergeClass($existing, $incoming),
            $incoming instanceof FunctionRecord && $existing instanceof FunctionRecord
                => $this->mergeFunction($existing, $incoming),
            $incoming instanceof ConstantRecord && $existing instanceof ConstantRecord
                => $this->mergeConstant($existing, $incoming),
            $incoming instanceof MemberRecord && $existing instanceof MemberRecord
                => $this->mergeMember($existing, $incoming),
            default => $incoming,
        });
    }

    private function mergeClass(ClassRecord $existing, ClassRecord $incoming): ClassRecord
    {
        foreach ($incoming->references() as $reference) {
            $existing->addReference($reference);
        }

        foreach ($incoming->implementations() as $implementation) {
            $existing->addImplementation(FullyQualifiedName::fromString($implementation));
        }

        if (null === $filePath = $incoming->filePath()) {
            return $existing;
        }

        $existing->setFilePath(TextDocumentUri::fromString($filePath));
        $existing->setStart($incoming->start());
        $existing->setEnd($incoming->end());
        $existing->setFlags($incoming->flags());

        $existing->clearImplemented();
        foreach ($incoming->implements() as $implemented) {
            $existing->addImplements(FullyQualifiedName::fromString($implemented));
        }

        if (null !== $type = $incoming->type()) {
            $existing->setType($type);
        }

        return $existing;
    }

    private function mergeFunction(FunctionRecord $existing, FunctionRecord $incoming): FunctionRecord
    {
        foreach ($incoming->references() as $reference) {
            $existing->addReference($reference);
        }

        if (null === $filePath = $incoming->filePath()) {
            return $existing;
        }

        $existing->setFilePath(TextDocumentUri::fromString($filePath));
        $existing->setStart($incoming->start());
        $existing->setEnd($incoming->end());

        return $existing;
    }

    private function mergeConstant(ConstantRecord $existing, ConstantRecord $incoming): ConstantRecord
    {
        if (null === $filePath = $incoming->filePath()) {
            return $existing;
        }

        $existing->setFilePath(TextDocumentUri::fromString($filePath));
        $existing->setStart($incoming->start());
        $existing->setEnd($incoming->end());

        return $existing;
    }

    private function mergeMember(MemberRecord $existing, MemberRecord $incoming): MemberRecord
    {
        foreach ($incoming->references() as $reference) {
            $existing->addReference($reference);
        }

        return $existing;
    }
}
