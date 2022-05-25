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
        foreach ($this->versionToRendererMap as $version => $renderer) {
            if (0 === strpos($phpVersion, $version)) {
                return $renderer;
            }
        }

        return new WorseTypeRenderer74();
    }
}
