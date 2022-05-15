<?php

namespace Phpactor\Extension\Behat\Tests\Integration\Behat;

use Phpactor\Extension\Behat\Behat\BehatConfig;
use Phpactor\Extension\Behat\Behat\Context;
use Phpactor\Extension\Behat\Tests\IntegrationTestCase;

class BehatConfigTest extends IntegrationTestCase
{
    private BehatConfig $config;

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->config = new BehatConfig($this->workspace()->path('/behat.yml'));
    }

    public function testReturnsContexts(): void
    {
        $this->workspace()->put(
            'behat.yml',
            <<<'EOT'
                default:
                    suites:
                        default:
                            contexts:
                                - One
                                - Two
                EOT
        );


        $contexts = $this->config->contexts();
        self::assertCount(2, $contexts);
        $context = reset($contexts);
        assert($context instanceof Context);
        self::assertEquals('One', $context->class());
        self::assertEquals('default', $context->suite());
    }

    public function testReturnsContextsFromImportedFiles(): void
    {
        $this->workspace()->put(
            'one.yml',
            <<<'EOT'
                default:
                    suites:
                        default:
                            contexts:
                                - One
                                - Two
                EOT
        );
        $this->workspace()->put(
            'two.yml',
            <<<'EOT'
                default:
                    suites:
                        default:
                            contexts:
                                - Three
                                - Four
                EOT
        );
        $this->workspace()->put(
            'behat.yml',
            <<<'EOT'
                imports:
                    - one.yml
                    - two.yml
                EOT
        );


        $contexts = $this->config->contexts();
        self::assertCount(4, $contexts);
    }

    public function testDoesNotReturnContextsFromImportedFilesWithNoContexts(): void
    {
        $this->workspace()->put(
            'one.yml',
            <<<'EOT'
                EOT
        );
        $this->workspace()->put(
            'two.yml',
            <<<'EOT'
                EOT
        );
        $this->workspace()->put(
            'behat.yml',
            <<<'EOT'
                imports:
                    - one.yml
                    - two.yml
                EOT
        );


        $contexts = $this->config->contexts();
        self::assertCount(0, $contexts);
    }
}
