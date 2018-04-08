<?php

namespace Phpactor\Extension\Rpc\Response\Reference;

class FileReferences
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @var array
     */
    private $references = [];

    private function __construct(string $filePath, array $references)
    {
        $this->filePath = $filePath;

        foreach ($references as $reference) {
            $this->addReference($reference);
        }
    }

    public static function fromPathAndReferences($filePath, array $references)
    {
        return new self($filePath, $references);
    }

    public function toArray()
    {
        return [
            'file' => $this->filePath,
            'references' => array_map(function (Reference $reference) {
                return $reference->toArray();
            }, $this->references)
        ];
    }

    private function addReference(Reference $reference)
    {
        $this->references[] = $reference;
    }
}
