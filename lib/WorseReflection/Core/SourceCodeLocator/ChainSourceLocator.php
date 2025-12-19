<?php

namespace Phpactor\WorseReflection\Core\SourceCodeLocator;

use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ChainSourceLocator implements SourceCodeLocator
{
    /**
     * @var SourceCodeLocator[]
     */
    private array $locators = [];

    /**
     * @param SourceCodeLocator[] $sourceLocators
     */
    public function __construct(
        array $sourceLocators,
        private LoggerInterface $logger = new NullLogger(),
    ) {
        foreach ($sourceLocators as $sourceLocator) {
            $this->add($sourceLocator);
        }
    }

    public function locate(Name $name): TextDocument
    {
        $exception = new SourceNotFound(
            'No source locators registered with chain loader '.
            '(or source locator did not throw SourceNotFound exception'
        );

        foreach ($this->locators as $locator) {
            $start = microtime(true);
            try {
                $source = $locator->locate($name);
                $this->logger->debug(sprintf(
                    ' OK [%s] "%s" with locator "%s"',
                    number_format(microtime(true) - $start, 4),
                    $name,
                    get_class($locator)
                ));
                return $source;
            } catch (SourceNotFound $e) {
                $this->logger->debug(sprintf(
                    'NOK [%s] "%s" with locator "%s" : %s',
                    number_format(microtime(true) - $start, 4),
                    $name,
                    get_class($locator),
                    $e->getMessage()
                ));
                $exception = new SourceNotFound(sprintf(
                    'Could not find source with "%s"',
                    (string) $name
                ), 0, $e);
            }
        }

        throw $exception;
    }

    private function add(SourceCodeLocator $locator): void
    {
        $this->locators[] = $locator;
    }
}
