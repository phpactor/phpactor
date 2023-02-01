<?php

namespace Phpactor\Extension\LanguageServerPsalm\Tests\Model;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerPsalm\Model\DiagnosticsParser;
use RuntimeException;

class DiagnosticsParserTest extends TestCase
{
    /**
     * @dataProvider provideParse
     */
    public function testParse(string $psalmJson, int $count): void
    {
        self::assertCount($count, (new DiagnosticsParser())->parse($psalmJson, '/path/to.php'));
    }

    public function provideParse()
    {
        yield [
            <<<'EOT'
                [{"severity":"info","line_from":49,"line_to":49,"type":"TooManyArguments","message":"Too many arguments for Phpactor\\Extension\\LanguageServerPsalm\\Model\\PsalmConfig::__construct - expecting 1 but saw 2","file_name":"lib\/LanguageServerPhpstanExtension.php","file_path":"\/path\/to.php","snippet":"                new PsalmConfig($binPath, $container->getParameter(self::PARAM_LEVEL)),","selected_text":"new PsalmConfig($binPath, $container->getParameter(self::PARAM_LEVEL))","from":2040,"to":2110,"snippet_from":2024,"snippet_to":2111,"column_from":17,"column_to":87,"error_level":4,"shortcode":26,"link":"https:\/\/psalm.dev\/026","taint_trace":null}]
                EOT
            , 1
        ];
    }

    public function testExceptionOnNonJsonString(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('stdout was not JSON');
        (new DiagnosticsParser())->parse('stdout was not JSON', '/path/to.php');
    }
}
