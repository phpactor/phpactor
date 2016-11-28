<?php

namespace Phpactor\Tests\Unit\Complete;

use Phpactor\Reflection\ComposerReflector;
use Phpactor\Complete\Completer;
use BetterReflection\SourceLocator\Type\StringSourceLocator;
use BetterReflection\SourceLocator\Type\SingleFileSourceLocator;

class CompleterTest extends \PHPUnit_Framework_TestCase
{
    public function testComplete()
    {
        $source = file_get_contents(__DIR__ . '/source/test1.php') ;
        $completer = $this->getCompleter($source);
        $completer->complete($source, 16, 26);
    }

    private function getCompleter($file)
    {
        return new Completer(new ComposerReflector(new StringSourceLocator($file)));
    }
}
