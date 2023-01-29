<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\Tests\Model;

use Phpactor\Extension\LanguageServerPhpCsFixer\Exception\PhpCsFixerError;
use Phpactor\Extension\LanguageServerPhpCsFixer\Tests\PhpCsFixerTestCase;
use function Amp\ByteStream\buffer;
use function Amp\Promise\wait;
use function Amp\call;

class PhpCsFixerProcessTest extends PhpCsFixerTestCase
{
    public function testRun(): void
    {
        $phpCsFixer = $this->getPhpCsFixer();

        $process = call(function () use ($phpCsFixer) {
            $process = yield $phpCsFixer->run('--version');
            $stdout = yield buffer($process->getStdout());

            $this->assertStringContainsString('PHP CS Fixer ', $stdout, "Expected php-cs-fixer --version to return it's name followed with version, got '$stdout'");
        });

        wait($process);
    }

    public function testFix(): void
    {
        $phpCsFixer = $this->getPhpCsFixer();

        $correctFix = wait($phpCsFixer->fix(
            <<<EOF
                <?php

                \$foo = 'bar';

                EOF
            ,
            ['--using-cache=no', '--format', 'json']
        ));
        $correctFix = json_decode($correctFix, true, 16, JSON_THROW_ON_ERROR);
        $this->assertEmpty($correctFix['files']);

        $incorrectFix = wait($phpCsFixer->fix(
            <<<EOF
                <?php

                \$foo = "bar";
                EOF
        ));
        $this->assertNotEmpty($incorrectFix, 'Expected non empty output for incorrectly formatted input');

        $this->expectException(PhpCsFixerError::class);
        $incorrectFix = wait($phpCsFixer->fix('', ['--invalid-option']));
    }

    public function testDescribe(): void
    {
        $phpCsFixer = $this->getPhpCsFixer();

        $description = wait($phpCsFixer->describe('braces'));

        $this->assertIsString($description);
        $this->assertStringStartsWith('Description of', $description);

        $this->expectException(PhpCsFixerError::class);
        wait($phpCsFixer->describe('UNKNOWN_RULE'));
    }
}
