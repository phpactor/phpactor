<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Tests\Model;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerPhpstan\Model\Linter\PhpstanLinter;
use Phpactor\Extension\LanguageServerPhpstan\Model\PhpstanProcess;
use Phpactor\VersionResolver\ArbitrarySemVerResolver;
use Phpactor\VersionResolver\SemVersion;

class PhpstanLinterTest extends TestCase
{
    public function testLinterUsesTmpFileByDefault(): void
    {
        $filePathInProject = '/foo';
        $fileContent = '<file content that will be written to tmp file>';

        $phpstanProcess = $this->createMock(PhpstanProcess::class);
        $phpstanProcess->expects($this->once())
            ->method('editorModeAnalyse')
            ->with($filePathInProject, $this->callback(function (string $tempFile) use ($fileContent) {
                // Assert the temp file has the same content as the project file
                $this->assertStringEqualsFile($tempFile, $fileContent);

                // Explicitly confirm a temporary file is used.
                //
                // NOTE: We use implementation detail knowledge here on how the tempnam is created.
                //       This might not be necessary but provides an extra layer of safety and makes
                //       the test more declarative.
                //
                // NOTE: It is not guaranteed that the sys_get_temp_dir() will be the *actual* directory
                //       that is used. MacOs for example will create a directory prefixed with /private:
                //       /private/<sys_get_temp_dir>/<prefix>...
                //
                $expectedPathSegment = sys_get_temp_dir() . '/phpstanls';
                $this->assertStringContainsString($expectedPathSegment, $tempFile);

                return true;
            }));

        $linter = new PhpstanLinter(
            $phpstanProcess,
            new ArbitrarySemVerResolver(SemVersion::fromString('2.30.0'))
        );

        $linter->lint($filePathInProject, $fileContent);
    }

    public function testLinterUsesOriginalFilePathWhenTmpFileDisabled(): void
    {
        $originalFilePath = '/foo';

        $phpstanProcess = $this->createMock(PhpstanProcess::class);
        $phpstanProcess->expects($this->once())
            ->method('analyseInPlace')
            ->with($originalFilePath);

        $linter = new PhpstanLinter(
            phpstanProcess: $phpstanProcess,
            versionResolver: new ArbitrarySemVerResolver(SemVersion::fromString('1.0.0')),
            disableTmpFile: true,
        );

        $linter->lint($originalFilePath, '<file content that will be ignored and not used for a tmp file>');
    }

    public function testLinterUsesEditoMode(): void
    {
        $originalFilePath = '/foo';

        $phpstanProcess = $this->createMock(PhpstanProcess::class);
        $phpstanProcess->expects($this->once())
            ->method('editorModeAnalyse')
            ->with($originalFilePath);

        $linter = new PhpstanLinter(
            phpstanProcess: $phpstanProcess,
            versionResolver: new ArbitrarySemVerResolver(SemVersion::fromString('2.4.0')),
        );

        $linter->lint($originalFilePath, 'example');
    }
}
