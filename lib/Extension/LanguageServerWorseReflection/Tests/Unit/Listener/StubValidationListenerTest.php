<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\Tests\Unit\Listener;

use Phpactor\Extension\LanguageServerWorseReflection\Listener\StubValidationListener;
use Phpactor\Extension\LanguageServerWorseReflection\Tests\IntegrationTestCase;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;

class StubValidationListenerTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function testFileDoesntExist(): void
    {
        $paths = [
            $this->workspace()->path('foobar.php'),
        ];
        $builder = $this->createBuilder($paths);
        $tester = $builder->build();
        $tester->initialize();
        $notification = $builder->transmitter()->shiftNotification();
        self::assertNotNull($notification);
        self::assertIsString($notification->params['message'] ?? '');
        self::assertStringContainsString(
            'The following stubs could not be found',
            $notification->params['message'] ?? ''
        );
    }

    public function testNotAFile(): void
    {
        $this->workspace()->mkdir('foobar');

        $paths = [
            $this->workspace()->path('foobar'),
        ];
        $builder = $this->createBuilder($paths);
        $tester = $builder->build();
        $tester->initialize();
        $notification = $builder->transmitter()->shiftNotification();
        self::assertNotNull($notification);
        self::assertIsString($notification->params['message'] ?? '');
        self::assertStringContainsString(
            'The following stubs could not be found',
            $notification->params['message'] ?? ''
        );
    }

    public function testValidFiles(): void
    {
        $this->workspace()->put('foobar1.stub', '');
        $this->workspace()->put('foobar2.stub', '');

        $paths = [
            $this->workspace()->path('foobar1.stub'),
            $this->workspace()->path('foobar2.stub'),
        ];
        $builder = $this->createBuilder($paths);
        $tester = $builder->build();
        $tester->initialize();
        $notification = $builder->transmitter()->shiftNotification();
        self::assertNull($notification);
    }

    /**
     * @param list<string> $paths
     */
    private function createBuilder(array $paths): LanguageServerTesterBuilder
    {
        $builder = LanguageServerTesterBuilder::create();
        $listener = (new StubValidationListener(
            $builder->clientApi(),
            $paths,
        ));
        $builder->addListenerProvider($listener);
        return $builder;
    }
}
