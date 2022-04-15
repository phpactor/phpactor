<?php

namespace Phpactor\WorseReferenceFinder\Tests\Unit;

use Phpactor\ReferenceFinder\TypeLocation;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReferenceFinder\Tests\IntegrationTestCase;
use Phpactor\WorseReferenceFinder\WorseReflectionTypeLocator;

class WorseReflectionTypeLocatorTest extends IntegrationTestCase
{
    public function testLocatesType(): void
    {
        $location = $this->locate(
            <<<'EOT'
                // File: One.php
                // <?php class One {}
                // File: Two.php
                // <?php class Two {}
                EOT
        ,
            <<<'EOT'
                <?php

                class Foo
                {
                    /** 
                     * @var One
                     */
                    private $one;

                    public function bar()
                    {
                        $this->o<>ne;
                    }
                }
                EOT
        );
        self::assertEquals('One', $location->type()->__toString());
        self::assertEquals($this->workspace->path('One.php'), $location->location()->uri()->path());
        self::assertEquals(9, $location->location()->offset()->toInt());
    }

    public function testLocatesArrayType(): void
    {
        $location = $this->locate(
            <<<'EOT'
                // File: One.php
                // <?php class One {}
                // File: Two.php
                // <?php class Two {}
                EOT
        ,
            <<<'EOT'
                <?php

                class Foo
                {
                    /** 
                     * @var One[]
                     */
                    private $one;

                    public function bar()
                    {
                        $this->o<>ne;
                    }
                }
                EOT
        );
        self::assertEquals($this->workspace->path('One.php'), $location->location()->uri()->path());
        self::assertEquals(9, $location->location()->offset()->toInt());
    }

    public function testLocatesInterface(): void
    {
        $location = $this->locate(
            <<<'EOT'
                // File: One.php
                // <?php interface One {}
                // File: Two.php
                // <?php class Two {}
                EOT
        ,
            <<<'EOT'
                <?php

                class Foo
                {
                    /** 
                     * @var One
                     */
                    private $one;

                    public function bar()
                    {
                        $this->o<>ne;
                    }
                }
                EOT
        );
        self::assertEquals($this->workspace->path('One.php'), $location->location()->uri()->path());
        self::assertEquals(9, $location->location()->offset()->toInt());
    }

    public function testLocatesFirstUnion(): void
    {
        $location = $this->locate(
            <<<'EOT'
                // File: One.php
                // <?php interface One {}
                // File: Two.php
                // <?php class Two {}
                EOT
        ,
            <<<'EOT'
                <?php

                class Foo
                {
                    /** 
                     * @var Two|One
                     */
                    private $one;

                    public function bar()
                    {
                        $this->o<>ne;
                    }
                }
                EOT
        );
        self::assertEquals($this->workspace->path('Two.php'), $location->location()->uri()->path());
    }

    public function testLocatesFirstUnionWithNullAndScalar(): void
    {
        $location = $this->locate(
            <<<'EOT'
                // File: One.php
                // <?php interface One {}
                // File: Two.php
                // <?php class Two {}
                EOT
        ,
            <<<'EOT'
                <?php

                class Foo
                {
                    /** 
                     * @var string|null|Two|One
                     */
                    private $one;

                    public function bar()
                    {
                        $this->o<>ne;
                    }
                }
                EOT
        );
        self::assertEquals($this->workspace->path('Two.php'), $location->location()->uri()->path());
    }

    protected function locate(string $manifset, string $source): TypeLocation
    {
        [$source, $offset] = ExtractOffset::fromSource($source);

        $this->workspace->loadManifest($manifset);

        return (new WorseReflectionTypeLocator($this->reflector()))->locateTypes(
            TextDocumentBuilder::create($source)->language('php')->build(),
            ByteOffset::fromInt((int)$offset)
        )->first();
    }
}
