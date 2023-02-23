<?php

namespace Phpactor\Configurator\Tests\Integration;

use Phpactor\Configurator\ChangeSuggestor\TestChangeSuggestor;
use Phpactor\Configurator\Change\PhpactorConfigChange;
use Phpactor\Configurator\Change\PhpactorConfigChangeApplicator;
use Phpactor\Configurator\Changes;
use Phpactor\Configurator\ConfigManipulator;
use Phpactor\Configurator\Configurator;
use Phpactor\Configurator\Tests\IntegrationTestCase;

class ConfiguratorTest extends IntegrationTestCase
{
    public function testConfigurator(): void
    {
        $this->workspace()->path();

        $configurator = new Configurator([
            new TestChangeSuggestor(function (): Changes {
                return new Changes([
                    new PhpactorConfigChange('Symfony detected: enable Symfony extension', [
                        'symfony.enable' => true,
                        'indexer.ignore' => ['var'],
                    ])
                ]);
            }),
            new TestChangeSuggestor(function (): Changes {
                return new Changes([
                    new PhpactorConfigChange('PHPUnit detected: enable the PHPUnit extension', [
                        'phpunit.enable' => true,
                    ])
                ]);
            })
        ], [
            new PhpactorConfigChangeApplicator(new ConfigManipulator(
                'schemaPath.json',
                $this->workspace()->path('phpactor.json')
            ))
        ]);

        $changes = $configurator->suggestChanges();
        self::assertCount(2, $changes);

        $configurator->apply($changes);

        self::assertEquals([
            '$schema' => 'schemaPath.json',
            'symfony.enable' => true,
            'indexer.ignore' => ['var'],
            'phpunit.enable' => true,
        ], json_decode($this->workspace()->getContents('phpactor.json'), true));
    }
}
