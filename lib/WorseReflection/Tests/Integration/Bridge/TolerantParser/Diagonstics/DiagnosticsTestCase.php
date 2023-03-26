<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Diagonstics;

use Generator;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Diagnostics;
use Phpactor\WorseReflection\Core\SourceCodeLocator\BruteForceSourceLocator;
use Phpactor\WorseReflection\ReflectorBuilder;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;

abstract class DiagnosticsTestCase extends IntegrationTestCase
{
    /**
     * @dataProvider provideDiagnostic
     */
    public function testDiagnostic(string $path): void
    {
        $source = TextDocumentBuilder::fromUri($path)->build();
        $diagnostics = $this->diagnosticsFromSource($source);
        $method = sprintf('check%s', substr(basename($path), 0, (int)strrpos(basename($path), '.')));
        if (!method_exists($this, $method)) {
            $this->fail(sprintf('Diagnostic test method "%s" does not exist for file "%s"', $method, $path));
        }
        $this->$method($diagnostics);


        // the wrAssertType function in the source code will cause
        // an exception to be thrown if it fails
        $this->addToAssertionCount(1);
    }

    public function diagnosticsFromSource(string $source): Diagnostics
    {
        $source = TextDocumentBuilder::fromUnknown($source);
        $reflector = $this->createBuilder($source)->enableCache()->addDiagnosticProvider($this->provider())->build();
        $reflector->reflectOffset($source, mb_strlen($source));
        return $reflector->diagnostics($source);
    }

    public function diagnosticsFromManifest(string $manifest): Diagnostics
    {
        $this->workspace()->reset();
        $this->workspace()->loadManifest($manifest);
        $source = $this->workspace()->getContents('test.php');
        $source = TextDocumentBuilder::fromUnknown($source);

        $builder = ReflectorBuilder::create()
            ->withLogger($this->logger());

        $reflector = $builder->addLocator(
            new BruteForceSourceLocator(ReflectorBuilder::create()->build(), $this->workspace()->path())
        )->enableCache()->addDiagnosticProvider(
            $this->provider()
        )->build();

        return $reflector->diagnostics($source);
    }

    /**
     * @return Generator<mixed>mixed
     */
    public function provideDiagnostic(): Generator
    {
        $shortName = substr(static::class, strrpos(__CLASS__, '\\') + 1, -4);
        $dir = __DIR__ . '/' . $shortName;
        if (!file_exists($dir)) {
            self::fail(sprintf('Diagnostic test dir "%s" does not exist', $dir));
        }
        foreach ((array)glob($dir . '/*.test') as $fname) {
            yield $shortName .' ' . basename((string)$fname) => [
                $fname
            ];
        }
    }

    abstract protected function provider(): DiagnosticProvider;
}
