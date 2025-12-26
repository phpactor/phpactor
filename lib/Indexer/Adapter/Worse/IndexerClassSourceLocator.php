<?php

namespace Phpactor\Indexer\Adapter\Worse;

use Phpactor\Indexer\Model\IndexAccess;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\SourceCodeLocator;

class IndexerClassSourceLocator implements SourceCodeLocator
{
    public function __construct(private readonly IndexAccess $index)
    {
    }


    public function locate(Name $name): TextDocument
    {
        if ($name->__toString() === '') {
            throw new SourceNotFound('Name is empty');
        }

        $record = $this->index->get(ClassRecord::fromName($name->__toString()));
        $filePath = $record->filePath();

        if (null === $filePath || !file_exists($filePath)) {
            throw new SourceNotFound(sprintf(
                'Class "%s" is indexed, but it does not exist at path "%s"!',
                $name->full(),
                $filePath
            ));
        }

        return TextDocumentBuilder::fromUri($filePath)->build();
    }
}
