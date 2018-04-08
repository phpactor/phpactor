<?php

namespace Phpactor\Tests\Unit\Extension\ClassMover\Command\Logger;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Phpactor\Extension\ClassMover\Command\Logger\SymfonyConsoleMoveLogger;
use Phpactor\ClassMover\Domain\SourceCode;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\ClassMover\FoundReferences;
use Phpactor\ClassMover\Domain\Reference\NamespaceReference;
use Phpactor\ClassMover\Domain\Reference\NamespacedClassReferences;
use Phpactor\ClassMover\Domain\Reference\ClassReference;
use Phpactor\ClassMover\Domain\Name\QualifiedName;
use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;
use Phpactor\ClassMover\Domain\Reference\ImportedNameReference;
use Phpactor\ClassMover\Domain\Name\Namespace_;
use Phpactor\ClassMover\Domain\Reference\Position;

class SymfonyConsoleMoveLoggerTest extends TestCase
{
    private $output;
    private $logger;

    public function setUp()
    {
        $this->output = new BufferedOutput();
        $this->logger = new SymfonyConsoleMoveLogger($this->output);
    }

    public function testReplacing()
    {
        $references = new FoundReferences(
            SourceCode::fromString(
                <<<'EOT'
<?php

namespace Acme;

class Foobar
{
    public function source(): SourceCode
    {
        return $this->source;
    }

    public function targetName(): FullyQualifiedName
    {
        return $this->name;
    }

    public function references(): NamespacedClassRefList
    {
        return $this->references;
    }
}
EOT
            ),
            FullyQualifiedName::fromString('Acme'),
            NamespacedClassReferences::fromNamespaceAndClassRefs(
                NamespaceReference::fromNameAndPosition(Namespace_::fromString('Foobar'), Position::fromStartAndEnd(10, 20)),
                [
                    ClassReference::fromNameAndPosition(
                        QualifiedName::fromString('Hello'),
                        FullyQualifiedName::fromString('Foobar\Hello'),
                        Position::fromStartAndEnd(18, 20),
                        ImportedNameReference::none(),
                        false
                    )
                ]
            )
        );

        $target = FullyQualifiedName::fromString('Hello\World');
        $this->logger->replacing(FilePath::fromString('/path/to/file/Something.php'), $references, $target);
        $output = $this->output->fetch();
        $this->assertContains('Hello => World', $output);
    }
}
