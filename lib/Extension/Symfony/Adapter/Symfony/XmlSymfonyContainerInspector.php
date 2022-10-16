<?php

namespace Phpactor\Extension\Symfony\Adapter\Symfony;

use DOMDocument;
use DOMElement;
use DOMNode;
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
            $service = $this->serviceFromEl($serviceEl);
            if (null === $service) {
                continue;
            }
            $services[] = $service;
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

    public function service(string $id): ?SymfonyContainerService
    {
        $list = $this->loadXPath()->query(sprintf("//symfony:service[@id='%s']", $id));
        if ($list === false) {
            return null;
        }
        foreach ($list as $serviceEl) {
            return $this->serviceFromEl($serviceEl);
        }
        return null;
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

    private function serviceFromEl(DOMNode $serviceEl): ?SymfonyContainerService
    {
        if (!$serviceEl instanceof DOMElement) {
            return null;
        }
        $id = $serviceEl->getAttribute('id');
        $class = $serviceEl->getAttribute('class');
        $public = $serviceEl->getAttribute('public');
        if ('true' !== $public) {
            return null;
        }
        if (empty($id) || empty($class)) {
            return null;
        }
        return new SymfonyContainerService(
            $id,
            TypeFactory::fromString($class),
        );
    }
}
