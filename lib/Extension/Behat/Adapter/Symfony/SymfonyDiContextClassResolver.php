<?php

namespace Phpactor\Extension\Behat\Adapter\Symfony;

use DOMDocument;
use DOMXPath;
use Phpactor\Extension\Behat\Behat\ContextClassResolver;
use Phpactor\Extension\Behat\Behat\Exception\CouldNotResolverContextClass;
use RuntimeException;

class SymfonyDiContextClassResolver implements ContextClassResolver
{
    /**
     * @var string
     */
    private $xmlPath;

    /**
     * @var array<string,string>
     */
    private $index = null;

    public function __construct(string $xmlPath)
    {
        $this->xmlPath = $xmlPath;
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
            $this->index[$serviceEl->getAttribute('id')] = $serviceEl->getAttribute('class');
        }
    }
}
