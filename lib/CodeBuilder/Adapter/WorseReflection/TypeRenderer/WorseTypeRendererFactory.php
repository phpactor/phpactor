<?php

namespace Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer;

final class WorseTypeRendererFactory
{
    /**
     * @param array<string,WorseTypeRenderer> $versionToRendererMap
     */
    public function __construct(private readonly array $versionToRendererMap)
    {
    }

    public function rendererFor(string $phpVersion): WorseTypeRenderer
    {
        foreach ($this->versionToRendererMap as $version => $renderer) {
            if (str_starts_with($phpVersion, $version)) {
                return $renderer;
            }
        }

        return new WorseTypeRenderer74();
    }
}
