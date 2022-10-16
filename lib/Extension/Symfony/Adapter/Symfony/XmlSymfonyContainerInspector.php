<?php

namespace Phpactor\Extension\Symfony\Adapter\Symfony;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Phpactor\Extension\Symfony\Model\SymfonyContainerInspector;
use Phpactor\Extension\Symfony\Model\SymfonyContainerParameter;
use Phpactor\Extension\Symfony\Model\SymfonyContainerService;
use Phpactor\WorseReflection\Core\TypeFactory;

class XmlSymfonyContainerInspector implements SymfonyContainerInspector
{
    private string $xmlPath;

    public function __construct(string $xmlPath)
    {
        $this->xmlPath = $xmlPath;
    }

    public function services(): array
    {
        $dom = $this->loadXPath();

        if (null === $dom) {
            return [];
        }

        $services = [];
        $serviceEls = $dom->query('//symfony:service');
        if (false === $serviceEls) {
            return [];
        }
        foreach ($serviceEls as $serviceEl) {
            if (!$serviceEl instanceof DOMElement) {
                continue;
            }
            $id = $serviceEl->getAttribute('id');
            $class = $serviceEl->getAttribute('class');
            $public = $serviceEl->getAttribute('public');
            if ('true' !== $public) {
                continue;
            }
            if (empty($id) || empty($class)) {
                continue;
            }
            $services[] = new SymfonyContainerService(
                $id,
                TypeFactory::fromString($class),
            );
        }

        return $services;
    }

    public function parameters(): array
    {
        $dom = $this->loadXPath();

        if (null === $dom) {
            return [];
        }

        $parameters = [];
        $parameterEls = $dom->query('//symfony:parameter');
        if (false === $parameterEls) {
            return [];
        }
        foreach ($parameterEls as $parameterEl) {
            if (!$parameterEl instanceof DOMElement) {
                continue;
            }
            $key = $parameterEl->getAttribute('key');
            $value = $parameterEl->nodeValue;
            if (empty($key) || !is_string($value)) {
                continue;
            }
            $parameters[] = new SymfonyContainerParameter(
                $key,
                TypeFactory::fromValue($value),
            );
        }

        return $parameters;
    }

    private function loadXPath(): ?DOMXPath
    {
        if (!file_exists($this->xmlPath)) {
            return null;
        }
        $dom = new DOMDocument();
        $dom->load($this->xmlPath);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('symfony', 'http://symfony.com/schema/dic/services');
        return $xpath;
    }

    public function service(string $id): ?SymfonyContainerService
    {
    }
}
