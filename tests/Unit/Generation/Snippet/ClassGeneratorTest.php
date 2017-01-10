<?php

namespace Phpactor\Tests\Unit\Generation\Snippet;

use Phpactor\Composer\ClassNameResolver;
use Phpactor\CodeContext;
use Phpactor\Generation\Snippet\ClassGenerator;
use Phpactor\Composer\ClassFqn;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClassGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClassNameResolver
     */
    private $resolver;

    /**
     * @var ClassGenerator
     */
    private $generator;

    public function setUp()
    {
        $this->resolver = $this->prophesize(ClassNameResolver::class);
        $this->generator = new ClassGenerator($this->resolver->reveal());
    }

    /**
     * @dataProvider provideGenerate
     */
    public function testGenerate(array $options, $expectedSnippet)
    {
        $filename = 'foo/foobar.php';
        $this->resolver->resolve($filename)->willReturn(ClassFqn::fromString('Foo\\Bar'));

        $resolver = new OptionsResolver();
        $this->generator->configureOptions($resolver);
        $options = $resolver->resolve($options);

        $snippet = $this->generator->generate(CodeContext::create($filename, '', 0), $options);
        $this->assertEquals($expectedSnippet, $snippet);
    }

    public function provideGenerate()
    {
        return [
            [
                [
                ],
                <<<EOT
<?php

namespace Foo;

class Bar
{
}
EOT
            ],
            [
                [
                    'type' => 'class',
                ],
                <<<EOT
<?php

namespace Foo;

class Bar
{
}
EOT
            ],
            [
                [
                    'type' => 'trait',
                ],
                <<<EOT
<?php

namespace Foo;

trait Bar
{
}
EOT
            ],
            [
                [
                    'type' => 'interface',
                ],
                <<<EOT
<?php

namespace Foo;

interface Bar
{
}
EOT
            ],
        ];
    }
}
