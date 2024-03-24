<?php

namespace Phpactor\Extension\Behat\Adapter\Symfony;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Phpactor\Extension\Behat\Behat\ContextClassResolver;
use Phpactor\Extension\Behat\Behat\Exception\CouldNotResolverContextClass;
use RuntimeException;

class SymfonyDiContextClassResolver implements ContextClassResolver
{
    /**
     * @var array<string,string>
     */
    private ?array $index = null;

    public function __construct(private string $xmlPath)
    {
    }

    public function resolve(string $className): string
    {
        $this->loadIndex();

        if (isset($this->index[$className])) {
            return $this->index[$className];
        }

        throw new CouldNotResolverContextClass(sprintf(
            'Could not resolve context from Symfony container "%s"',
            $this->xmlPath
        ));
    }

    private function loadIndex(): void
    {
        if ($this->index !== null) {
            return;
        }

        if (!file_exists($this->xmlPath)) {
            throw new RuntimeException(sprintf(
                'Symfony DI XML file "%s" does not exist',
                $this->xmlPath
            ));
        }
        $dom = new DOMDocument('1.0');
        $dom->loadXML((string)file_get_contents($this->xmlPath));
        $query = new DOMXPath($dom);
        $query->registerNamespace('s', 'http://symfony.com/schema/dic/services');
        /** @phpstan-ignore-next-line */
        foreach ($query->query('//s:service') as $serviceEl) {
            if (!$serviceEl instanceof DOMElement) {
                continue;
            }
            $this->index[(string)$serviceEl->getAttribute('id')] = (string)$serviceEl->getAttribute('class');
        }
    }
}
