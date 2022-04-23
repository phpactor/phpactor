<?php

namespace Phpactor\WorseReferenceFinder\Tests\Unit;

use Phpactor\ReferenceFinder\TypeLocations;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReferenceFinder\Tests\IntegrationTestCase;
use Phpactor\WorseReferenceFinder\WorseReflectionTypeLocator;

class WorseReflectionTypeLocatorTest extends IntegrationTestCase
{
    public function testLocatesType(): void
    {
        $typeLocations = $this->locate(
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
        self::assertEquals('One', $typeLocations->first()->type()->__toString());
        self::assertEquals($this->workspace->path('One.php'), $typeLocations->first()->location()->uri()->path());
        self::assertEquals(9, $typeLocations->first()->location()->offset()->toInt());
    }

    public function testLocatesArrayType(): void
    {
        $typeLocations = $this->locate(
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
        self::assertEquals($this->workspace->path('One.php'), $typeLocations->first()->location()->uri()->path());
        self::assertEquals(9, $typeLocations->first()->location()->offset()->toInt());
    }

    public function testLocatesFromArray(): void
    {
        $typeLocations = $this->locate(
            <<<'EOT'
                // File: One.php
                // <?php class One {}
                // File: Two.php
                // <?php class Two {}
                EOT
        ,
            <<<'EOT'
                <?php

                $ones = [new One()];
                $o<>nes;

                EOT
        );
        self::assertEquals($this->workspace->path('One.php'), $typeLocations->first()->location()->uri()->path());
        self::assertEquals(9, $typeLocations->first()->location()->offset()->toInt());
    }

    public function testLocatesInterface(): void
    {
        $typeLocations = $this->locate(
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
        self::assertEquals($this->workspace->path('One.php'), $typeLocations->first()->location()->uri()->path());
        self::assertEquals(9, $typeLocations->first()->location()->offset()->toInt());
    }

    public function testLocatesUnion(): void
    {
        $typeLocations = $this->locate(
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
        self::assertEquals($this->workspace->path('Two.php'), $typeLocations->atIndex(0)->location()->uri()->path());
        self::assertEquals($this->workspace->path('One.php'), $typeLocations->atIndex(1)->location()->uri()->path());
    }

    public function testLocatesFirstUnionWithNullAndScalar(): void
    {
        $typeLocations = $this->locate(
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
        self::assertEquals($this->workspace->path('Two.php'), $typeLocations->first()->location()->uri()->path());
    }

    protected function locate(string $manifset, string $source): TypeLocations
    {
        [$source, $offset] = ExtractOffset::fromSource($source);

        $this->workspace->loadManifest($manifset);

        return (new WorseReflectionTypeLocator($this->reflector()))->locateTypes(
            TextDocumentBuilder::create($source)->language('php')->build(),
            ByteOffset::fromInt((int)$offset)
        );
    }
}
