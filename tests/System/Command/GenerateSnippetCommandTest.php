<?php

namespace Phpactor\Tests\System\Command;

use Phpactor\Tests\System\SystemTestCase;

class GenerateSnippetCommandTest extends SystemTestCase
{
    /**
     * @dataProvider provideGenerate
     */
    public function testGenerate($generator)
    {
        $process = $this->exec('generate:snippet ' . $generator . ' ' . __DIR__ . '/../../Functional/Example/ClassOne.php');
        $this->assertSuccess($process);
    }

    public function provideGenerate()
    {
        return [
            [
                'implement_missing_methods',
            ],
        ];
    }
}


