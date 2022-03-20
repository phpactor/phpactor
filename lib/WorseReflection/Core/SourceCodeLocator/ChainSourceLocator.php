<?php

namespace Phpactor\WorseReflection\Core\SourceCodeLocator;

use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ChainSourceLocator implements SourceCodeLocator
{
    /**
     * @var SourceCodeLocator[]
     */
    private array $locators = [];
    
    private LoggerInterface $logger;

    public function __construct(array $sourceLocators, ?LoggerInterface $logger = null)
    {
        foreach ($sourceLocators as $sourceLocator) {
            $this->add($sourceLocator);
        }
        $this->logger = $logger ?: new NullLogger();
    }

    public function locate(Name $name): SourceCode
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
