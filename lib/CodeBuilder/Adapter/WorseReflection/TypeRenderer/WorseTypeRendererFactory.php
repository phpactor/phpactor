<?php

namespace Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer;

final class WorseTypeRendererFactory
{
    /**
     * @var array<string,WorseTypeRenderer>
     */
    private array $versionToRendererMap;

    /**
     * @param array<string,WorseTypeRenderer> $versionToRendererMap
     */
    public function __construct(array $versionToRendererMap)
    {
        $this->versionToRendererMap = $versionToRendererMap;
    }

    public function rendererFor(string $phpVersion): WorseTypeRenderer
    {
        if (!isset($this->versionToRendererMap[$phpVersion])) {
            return new WorseTypeRenderer74();
        }

        return $this->versionToRendererMap[$phpVersion];
    }
}
