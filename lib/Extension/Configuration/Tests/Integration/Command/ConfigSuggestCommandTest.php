<?php

namespace Phpactor\Extension\Configuration\Tests\Integration\Command;

use Phpactor\Configurator\Model\JsonConfig;
use Phpactor\Extension\Configuration\Tests\IntegrationTestCase;
use Phpactor\Extension\LanguageServerPhpstan\LanguageServerPhpstanExtension;

class ConfigSuggestCommandTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function testSuggestWhereFileNotExisting(): void
    {
        $this->process(['config:auto'])->mustRun();
        $this->addToAssertionCount(1);
    }

    public function testSuggestPhpstan(): void
    {
        $this->workspace()->put('composer.json', '{"require-dev": {"phpstan/phpstan": "^1.0"}}');
        $process = $this->process(['config:auto']);
        $process->mustRun();
        self::assertStringContainsString('1 changes applied', $process->getErrorOutput());
        $this->addToAssertionCount(1);
        self::assertTrue(JsonConfig::fromPath($this->workspace()->path('.phpactor.json'))->has(LanguageServerPhpstanExtension::PARAM_ENABLED));
    }

    public function testDoNotSuggestPhpstanIfAlreadyDisabled(): void
    {
        $this->workspace()->put('composer.json', '{"require-dev": {"phpstan/phpstan": "^1.0"}}');
        $this->workspace()->put('.phpactor.json', sprintf('{"%s": false}', LanguageServerPhpstanExtension::PARAM_ENABLED));
        $process = $this->process(['config:auto']);
        $process->mustRun();
        self::assertStringContainsString('0 changes applied', $process->getErrorOutput());
        $this->addToAssertionCount(1);
        self::assertTrue(JsonConfig::fromPath($this->workspace()->path('.phpactor.json'))->has(LanguageServerPhpstanExtension::PARAM_ENABLED));
    }
}
