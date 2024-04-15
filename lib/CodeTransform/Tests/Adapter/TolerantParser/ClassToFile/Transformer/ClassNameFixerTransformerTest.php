<?php

namespace Phpactor\CodeTransform\Tests\Adapter\TolerantParser\ClassToFile\Transformer;

use Generator;
use Phpactor\ClassFileConverter\Adapter\Composer\ComposerFileToClass;
use Phpactor\CodeTransform\Adapter\TolerantParser\ClassToFile\Transformer\ClassNameFixerTransformer;
use Phpactor\CodeTransform\Domain\Exception\TransformException;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Tests\Adapter\AdapterTestCase;
use Phpactor\TestUtils\Workspace;
use function Amp\Promise\wait;

class ClassNameFixerTransformerTest extends AdapterTestCase
{
    private static $composerAutoload;

    /**
     * @dataProvider provideFixClassName
     */
    public function testFixClassName(string $filePath, string $test, int $diagnosticCount): void
    {
        $workspace = $this->workspace();
        $workspace->reset();
        $workspace->loadManifest((string)file_get_contents(__DIR__ . '/fixtures/' . $test));
        $expected = $workspace->getContents('expected');

        $transformer = $this->createTransformer($workspace);

        $source = SourceCode::fromStringAndPath(
            $workspace->getContents($filePath),
            $this->workspace()->path($filePath)
        );

        $diagnostics = wait($transformer->diagnostics($source));
        $this->assertCount($diagnosticCount, $diagnostics);
        $transformed = wait($transformer->transform($source));

        $this->assertEquals(trim($expected), trim($transformed->apply($source)));
    }

    /**
     * @return Generator<string,array{string,string,int}>
     */
    public function provideFixClassName(): Generator
    {
        yield 'no op' => [
            'FileOne.php',
            'fixNamespace0.test',
            0
        ];
        yield 'fix file with missing namespace' => [
            'PathTo/FileOne.php',
            'fixNamespace1.test',
            1
        ];
        yield 'fix file with namespace' => [
            'PathTo/FileOne.php',
            'fixNamespace2.test',
            1
        ];
        yield 'fix class name' => [
            'FileOne.php',
            'fixNamespace3.test',
            1
        ];
        yield 'fix class name with same line bracket' => [
            'FileOne.php',
            'fixNamespace4.test',
            1
        ];
        yield 'fix class name and namespace' => [
            'Phpactor/Test/Foobar/FileOne.php',
            'fixNamespace5.test',
            2
        ];
    }

    public function testThrowsExceptionIfSourceCodeHasNoPath(): void
    {
        $this->expectException(TransformException::class);
        $this->expectExceptionMessage('Source is not a file');
        $transformer = $this->createTransformer($this->workspace());
        $transformed = wait($transformer->transform(SourceCode::fromString('hello')));
    }

    public function testOnEmptyFile(): void
    {
        $workspace = $this->workspace();
        $workspace->reset();
        $workspace->loadManifest(file_get_contents(__DIR__ . '/fixtures/fixNamespace1.test'));
        $source = $workspace->getContents('PathTo/FileOne.php');
        $expected = $workspace->getContents('expected');
        $transformer = $this->createTransformer($workspace);
        $source = SourceCode::fromStringAndPath('', $this->workspace()->path('/PathTo/FileOne.php'));
        $transformed = wait($transformer->transform($source));
        $this->assertEquals(<<<'EOT'
            <?php

            namespace PathTo;

            EOT
            , (string) $transformed->apply($source));
    }

    private function initComposer(Workspace $workspace)
    {
        if (self::$composerAutoload) {
            return self::$composerAutoload;
        }

        $composer = <<<'EOT'
            {
            "autoload": {
                "psr-4": {
                    "": ""
                }
            }
            }
            EOT
        ;
        file_put_contents($workspace->path('/composer.json'), $composer);
        $cwd = getcwd();
        chdir($workspace->path('/'));
        exec('composer dumpautoload');
        chdir($cwd);
        self::$composerAutoload = require_once($workspace->path('/vendor/autoload.php'));

        return $this->initComposer($workspace);
    }

    private function createTransformer(Workspace $workspace): ClassNameFixerTransformer
    {
        $autoload = $this->initComposer($workspace);
        $fileToClass = new ComposerFileToClass($autoload);
        $transformer = new ClassNameFixerTransformer($fileToClass);
        return $transformer;
    }
}
