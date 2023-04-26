<?php

namespace Phpactor\Extension\Rpc\Response\Reference;

class FileReferences
{
    private array $references = [];

    private function __construct(private string $filePath, array $references)
    {
        foreach ($references as $reference) {
            $this->addReference($reference);
        }
    }

    public static function fromPathAndReferences($filePath, array $references): self
    {
        return new self($filePath, $references);
    }

    /**
     * @return array{
     *  file: string,
     *  references: array<
     *    array{
     *      start: int,
     *      end: int,
     *      line: string,
     *      line_no: int,
     *      col_no: int|null
     *    }
     *  >
     * }
    */
    public function toArray(): array
    {
        return [
            'file' => $this->filePath,
            'references' => array_map(function (Reference $reference) {
                return $reference->toArray();
            }, $this->references)
        ];
    }

    private function addReference(Reference $reference): void
    {
        $this->references[] = $reference;
    }
}
