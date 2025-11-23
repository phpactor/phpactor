<?php

namespace Phpactor\Extension\WorseReflection\Tests\Command;

use Microsoft\PhpParser\Parser;
use Phpactor\Extension\WorseReflection\Command\DumpAstCommand;
use PHPUnit\Framework\TestCase;
use Phpactor\TestUtils\Workspace;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class DumpAstCommandTest extends TestCase
{
    public function testDumpAstCommand(): void
    {
        $output = new BufferedOutput();
        $workspace = new Workspace(__DIR__ . '/../Workspace');
        $workspace->reset();
        $workspace->put('test.php', '<?php echo "hello";');
        $exitCode = (new DumpAstCommand(new Parser()))->run(new ArrayInput([
            'path' => $workspace->path('test.php')
        ]), $output);
        self::assertEquals(0, $exitCode);
        self::assertStringContainsString('Parsing time', $output->fetch());
    }
}
