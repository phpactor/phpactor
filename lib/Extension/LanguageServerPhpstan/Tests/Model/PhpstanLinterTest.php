<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Tests\Model;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerPhpstan\Model\Linter\PhpstanLinter;
use Phpactor\Extension\LanguageServerPhpstan\Model\PhpstanProcess;

class PhpstanLinterTest extends TestCase
{
    public function testLinterUsesTmpFileByDefault(): void
    {
        $originalFilePath = '/foo';
        $expectedFileContent = '<file content that will be written to tmp file>';

        $phpstanProcess = $this->createMock(PhpstanProcess::class);
        $phpstanProcess->expects($this->once())
            ->method('analyse')
            ->with($this->callback(function ($analyzedFilePath) use ($originalFilePath, $expectedFileContent) {

                // Infer that a temporary file was used by confirming that the PHPStan
                // does not get the original file path passed.
                $this->assertNotEquals($originalFilePath, $analyzedFilePath);

                // Confirm the file content was written to the temporary file.
                $this->assertStringEqualsFile($analyzedFilePath, $expectedFileContent);

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
                $this->assertStringContainsString($expectedPathSegment, $analyzedFilePath);

                return true;
            }));

        $linter = new PhpstanLinter($phpstanProcess);

        $linter->lint($originalFilePath, $expectedFileContent);
    }

    public function testLinterUsesOriginalFilePathWhenTmpFileDisabled(): void
    {
        $originalFilePath = '/foo';

        $phpstanProcess = $this->createMock(PhpstanProcess::class);
        $phpstanProcess->expects($this->once())
            ->method('analyse')
            ->with($originalFilePath);

        $linter = new PhpstanLinter(
            process: $phpstanProcess,
            disableTmpFile: true,
        );

        $linter->lint($originalFilePath, '<file content that will be ignored and not used for a tmp file>');
    }
}
