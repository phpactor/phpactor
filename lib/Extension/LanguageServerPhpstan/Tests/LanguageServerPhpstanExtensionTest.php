<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Tests;

use Phpactor\Container\Container;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\LanguageServerPhpstan\Model\Linter;
use Phpactor\Extension\LanguageServerPhpstan\Model\Linter\PhpstanLinter;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\LanguageServerPhpstan\LanguageServerPhpstanExtension;
use PHPUnit\Framework\TestCase;

class LanguageServerPhpstanExtensionTest extends TestCase
{
    public function testParam_tmp_file_disabled(): void
    {
        // Case: Enabled by default
        $linter = $this->getLinter([]);
        $this->assertFalse($linter->isTmpFileDisabled());

        // Case: Enable via param
        $linter = $this->getLinter([LanguageServerPhpstanExtension::PARAM_TMP_FILE_DISABLED => false]);
        $this->assertFalse($linter->isTmpFileDisabled());

        // Case: Disable via param
        $linter = $this->getLinter([LanguageServerPhpstanExtension::PARAM_TMP_FILE_DISABLED => true]);
        $this->assertTrue($linter->isTmpFileDisabled());
    }

    /**
     * @param array<string, mixed> $params
     */
    private function getLinter(array $params = []): PhpstanLinter
    {
        $container = $this->getContainer($params);

        $linter = $container->get(Linter::class);

        $this->assertInstanceOf(PhpstanLinter::class, $linter);

        return $linter;
    }

    /**
     * @param array<string, mixed> $params
     */
    private function getContainer(array $params = []): Container
    {
        return PhpactorContainer::fromExtensions(
            [
                FilePathResolverExtension::class,
                LoggingExtension::class,
                LanguageServerPhpstanExtension::class
            ],
            $params
        );
    }
}
