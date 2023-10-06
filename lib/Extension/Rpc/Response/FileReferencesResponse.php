<?php

namespace Phpactor\Extension\Rpc\Response;

use Phpactor\Extension\Rpc\Response;
use Phpactor\Extension\Rpc\Response\Reference\FileReferences;
use Phpactor\Extension\Rpc\Response\Reference\Reference;

class FileReferencesResponse implements Response
{
    /**
    * @param array<FileReferences> $references
    */
    public function __construct(private array $references)
    {
    }

    public static function fromArray(array $array)
    {
        $references = [];
        foreach ($array as $fileAndReferences) {
            $references[] = FileReferences::fromPathAndReferences(
                $fileAndReferences['file'],
                array_map(function (array $reference) {
                    return Reference::fromStartEndLineNumberLineAndCol($reference['start'], $reference['end'], $reference['line_no'], $reference['line'] ?? '', $reference['col_no']);
                }, $fileAndReferences['references'])
            );
        }

        return new self($references);
    }

    public function name(): string
    {
        return 'file_references';
    }

    public function parameters(): array
    {
        return [
            'file_references' => array_map(function (FileReferences $fileReferences) {
                return $fileReferences->toArray();
            }, $this->references)
        ];
    }

    public function references(): array
    {
        return $this->references;
    }
}
