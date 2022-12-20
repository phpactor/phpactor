<?php

namespace Phpactor\Extension\Laravel\Adapter\Laravel;

use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ClassType;

class LaravelContainerInspector
{
    public function __construct(private string $xmlPath)
    {
    }

    public function service(string $id): ?ClassType
    {
        foreach ($this->services() as $short => $service) {
            if ($short === $id || $service === $id) {
                return TypeFactory::fromString($service);
            }
        }
        return null;
    }


    public function services(): array
    {
        $serviceCache = require $this->xmlPath;

        $services = [];

        foreach ($serviceCache['providers'] as $provider) {
            $services[$provider] = $provider;
        }

        return $services;
    }
}
