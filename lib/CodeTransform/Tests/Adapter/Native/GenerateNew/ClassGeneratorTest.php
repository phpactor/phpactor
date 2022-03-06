<?php

namespace Phpactor\CodeTransform\Tests\Adapter\Native\GenerateNew;

use Phpactor\CodeTransform\Tests\Adapter\AdapterTestCase;
use Phpactor\CodeTransform\Domain\ClassName;
use Phpactor\CodeTransform\Adapter\Native\GenerateNew\ClassGenerator;

class ClassGeneratorTest extends AdapterTestCase
{
    /**
     * It should generate a class
     */
    public function testGenerateClass(): void
    {
        $className = ClassName::fromString('Acme\\Blog\\Post');
        $generator = new ClassGenerator($this->renderer());
        $code = $generator->generateNew($className);

        $this->assertEquals(<<<'EOT'
            <?php

            namespace Acme\Blog;

            class Post
            {
            }
            EOT
        , (string) $code);
    }
}
