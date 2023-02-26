<?php

namespace Phpactor\Tests\System\Configuration;

use Closure;
use Generator;
use Phpactor\Configurator\Model\JsonConfig;
use Phpactor\Extension\LanguageServerPhpCsFixer\LanguageServerPhpCsFixerExtension;
use Phpactor\Extension\LanguageServerPhpstan\LanguageServerPhpstanExtension;
use Phpactor\Extension\LanguageServerPsalm\LanguageServerPsalmExtension;
use Phpactor\Tests\IntegrationTestCase;

class ConfigSuggestCommandTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function testSuggestWhereFileNotExisting(): void
    {
        $this->phpactor(['config:auto'])->mustRun();
        $this->addToAssertionCount(1);
    }
    /**
     * @param array<string,mixed> $composerJson
     * @param Closure(JsonConfig): void $assertion
     * @dataProvider provideSuggest
     */
    public function testSuggest(array $composerJson, Closure $assertion): void
    {
        $this->workspace()->put('composer.json', (string)json_encode($composerJson));
        $phpactor = $this->phpactor(['config:auto']);
        $phpactor->mustRun();
        self::assertStringContainsString('1 changes applied', $phpactor->getErrorOutput());
        $this->addToAssertionCount(1);
        $assertion(JsonConfig::fromPath($this->workspace()->path('.phpactor.json')));
    }
    /**
     * @return Generator<array{array<string,mixed>,Closure(JsonConfig): void}>
     */
    public function provideSuggest(): Generator
    {
        yield 'phpstan' => [
            ['require-dev' => ['phpstan/phpstan' => '^1.0']],
            function (JsonConfig $phpactorConfig): void {
                self::assertTrue($phpactorConfig->has(LanguageServerPhpstanExtension::PARAM_ENABLED));
            }
        ];
        yield 'psalm' => [
            ['require-dev' => ['vimeo/psalm' => '^1.0']],
            function (JsonConfig $phpactorConfig): void {
                self::assertTrue($phpactorConfig->has(LanguageServerPsalmExtension::PARAM_ENABLED));
            }
        ];
        yield 'php-cs-fixer' => [
            ['require-dev' => ['friendsofphp/php-cs-fixer' => '^1.0']],
            function (JsonConfig $phpactorConfig): void {
                self::assertTrue($phpactorConfig->has(LanguageServerPhpCsFixerExtension::PARAM_ENABLED));
            }
        ];
    }

    public function testDoNotSuggestPhpstanIfAlreadyDisabled(): void
    {
        $this->workspace()->put('composer.json', '{"require-dev": {"phpstan/phpstan": "^1.0"}}');
        $this->workspace()->put('.phpactor.json', sprintf('{"%s": false}', LanguageServerPhpstanExtension::PARAM_ENABLED));
        $phpactor = $this->phpactor(['config:auto']);
        $phpactor->mustRun();
        self::assertStringContainsString('0 changes applied', $phpactor->getErrorOutput());
        $this->addToAssertionCount(1);
        self::assertTrue(JsonConfig::fromPath($this->workspace()->path('.phpactor.json'))->has(LanguageServerPhpstanExtension::PARAM_ENABLED));
    }
}
