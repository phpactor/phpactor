<?php

namespace Phpactor\Extension\LanguageServer\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\Container\Container;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\LanguageServer\Tests\Example\TestExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\TestUtils\Workspace;
use RuntimeException;

class LanguageServerTestCase extends TestCase
{
    protected function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/../Workspace');
    }
    /**
     * @param array<string,mixed> $params
     */
    protected function createContainer(array $params = []): Container
    {
        return PhpactorContainer::fromExtensions([
            TestExtension::class,
            ConsoleExtension::class,
            LanguageServerExtension::class,
            LoggingExtension::class,
            FilePathResolverExtension::class
        ], array_merge([
            LanguageServerExtension::PARAM_CATCH_ERRORS => false,
        ], $params));
    }
    /**
     * @param array<string,mixed> $config
     */
    protected function createTester(?InitializeParams $params = null, array $config = []): LanguageServerTester
    {
        $builder = $this->createContainer(array_merge([
            LanguageServerExtension::PARAM_DIAGNOSTIC_OUTSOURCE => false,
        ], $config))->get(
            LanguageServerBuilder::class
        );

        $this->assertInstanceOf(LanguageServerBuilder::class, $builder);

        return $builder->tester($params ?? ProtocolFactory::initializeParams($this->workspace()->path('/')));
    }

    protected function assertSuccess(ResponseMessage $response): void
    {
        if (!$response->error) {
            return;
        }

        throw new RuntimeException(sprintf(
            'Response was not successful: [%s] %s: %s',
            $response->error->code,
            $response->error->message,
            $response->error->data
        ));
    }
}
