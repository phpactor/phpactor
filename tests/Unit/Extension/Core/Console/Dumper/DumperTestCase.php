<?php

namespace Phpactor\Tests\Unit\Extension\Core\Console\Dumper;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

abstract class DumperTestCase extends TestCase
{
    protected function dump(array $data)
    {
        $output =  new BufferedOutput();
        $this->dumper()->dump($output, $data);

        return $output->fetch();
    }

    abstract protected function dumper();
}
