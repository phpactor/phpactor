<?php

namespace Phpactor\Tests\UserInterface\Console\Command;

use Phpactor\Tests\UserInterface\SystemTestCase;

class ClassNewCommandTest extends SystemTestCase
{
    /**
     * Application level smoke tests
     *
     * @dataProvider provideSmokeSuccess
     */
    public function testNewClass($command, $expected)
    {
        $process = $this->phpactor($command);
        $this->assertSuccess($process);

        $this->assertEquals($expected, trim($process->getOutput()));
    }

    public function provideSmokeSuccess()
    {
        return [
            'New class' => [
                'class:new lib/Badger/Carnivorous.php --no-interaction',
                <<<'EOT'
<?php

namespace Animals\Badger;

class Carnivorous
{
}
EOT
            ],
        ];
    }
}

