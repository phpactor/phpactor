<?php

namespace Phpactor\Tests\Unit\Console\Logger;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Phpactor\UserInterface\Console\Logger\SymfonyConsoleMoveLogger;
use DTL\ClassMover\Domain\SourceCode;
use DTL\ClassMover\Domain\FullyQualifiedName;
use DTL\ClassMover\Domain\NamespacedClassRefList;
use DTL\ClassMover\Domain\NamespaceRef;
use DTL\ClassMover\Domain\Position;
use DTL\ClassMover\Domain\QualifiedName;
use DTL\ClassMover\Domain\ImportedNameRef;
use DTL\ClassMover\Domain\FoundReferences;
use DTL\ClassMover\Domain\SourceNamespace;
use DTL\ClassMover\Domain\ClassRef;
use DTL\Filesystem\Domain\FilePath;

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
            SourceCode::fromString(<<<'EOT'
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
            ), FullyQualifiedName::fromString('Acme'),
            NamespacedClassRefList::fromNamespaceAndClassRefs(
                NamespaceRef::fromNameAndPosition(SourceNamespace::fromString('Foobar'), Position::fromStartAndEnd(10, 20)),
                [
                    ClassRef::fromNameAndPosition(
                        QualifiedName::fromString('Hello'),
                        FullyQualifiedName::fromString('Foobar\Hello'),
                        Position::fromStartAndEnd(18, 20),
                        ImportedNameRef::none(),
                        false
                    )
                ]
            )
        );

        $this->logger->replacing(FilePath::fromPathInCurrentCwd('path/to/file/Something.php'), $references);
        $output = $this->output->fetch();
    }
}
