<?php

namespace Phpactor\Extension\LanguageServerBridge\Tests\Converter;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\TextDocument\FilesystemTextDocumentLocator;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\Extension\LanguageServerBridge\Converter\LocationConverter;
use Phpactor\Extension\LanguageServerBridge\Tests\IntegrationTestCase;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\Locations;
use Phpactor\LanguageServerProtocol\Location as LspLocation;
use Phpactor\TextDocument\TextDocumentUri;

class LocationConverterTest extends IntegrationTestCase
{
    private LocationConverter $converter;

    protected function setUp(): void
    {
        $this->workspace()->reset();

        $this->converter = new LocationConverter(new FilesystemTextDocumentLocator());
    }

    public function testConvertsPhpactorLocationsToLspLocations(): void
    {
        $this->workspace()->put('test.php', '012345678');

        $locations = new Locations([
            Location::fromPathAndOffsets($this->workspace()->path('test.php'), 2, 10)
        ]);

        $expected = [
            new LspLocation((string)TextDocumentUri::fromString($this->workspace()->path('test.php')), new Range(
                new Position(0, 2),
                new Position(0, 9),
            ))
        ];

        self::assertEquals($expected, $this->converter->toLspLocations($locations));
    }

    public function testIgnoresNonExistingFiles(): void
    {
        $this->workspace()->put('test.php', '12345678');

        $locations = new Locations([
            Location::fromPathAndOffsets($this->workspace()->path('test.php'), 2, 4),
            Location::fromPathAndOffsets($this->workspace()->path('test-no.php'), 2, 4)
        ]);

        $expected = [
            new LspLocation((string)TextDocumentUri::fromString($this->workspace()->path('test.php')), new Range(
                new Position(0, 2),
                new Position(0, 4),
            ))
        ];

        self::assertEquals($expected, $this->converter->toLspLocations($locations));
    }

    #[DataProvider('provideDiskLocations')]
    #[DataProvider('provideMultibyte')]
    #[DataProvider('provideOutOfRange')]
    public function testLocationToLspLocation(string $text, int $start, int $end, Range $expectedRange): void
    {
        $this->workspace()->put('test.php', $text);

        $location = Location::fromPathAndOffsets($this->workspace()->path('test.php'), $start, $end);

        $uri = (string)TextDocumentUri::fromString($this->workspace()->path('test.php'));

        self::assertEquals(
            expected: new LspLocation($uri, $expectedRange),
            actual: $this->converter->toLspLocation($location)
        );
    }

    /**
     * @return Generator<string, array{string, int, int, Range}>
     */
    public function provideOutOfRange(): Generator
    {
        yield 'out of upper range' => [
            '12345',
            10,
            15,
            $this->createRange(0, 5, 0, 5)
        ];
    }

    /**
     * @return Generator<string, array{string, int, int, Range}>
     */
    public function provideMultibyte(): Generator
    {
        yield '4 byte char 1st char' => [
            '😼😼😼😼😼',
            2,
            2,
            $this->createRange(0, 1, 0, 1)
        ];

        yield '4 byte char 2nd char' => [
            '😼😼😼😼😼',
            2,
            5,
            $this->createRange(0, 1, 0, 3)
        ];

        yield '4 byte char 4th char' => [
            '😼😼😼😼😼',
            2,
            16,
            $this->createRange(0, 1, 0, 8)
        ];
    }

    /**
     * @return Generator<string, array{string, int, int, Range}>
     */
    public function provideDiskLocations(): Generator
    {
        yield 'single line' => [
            '12345678',
            2,
            4,
            $this->createRange(0, 2, 0, 4)
        ];

        yield 'second line' => [
            "12\n345\n678",
            4,
            5,
            $this->createRange(1, 1, 1, 2)
        ];

        yield 'third line first char' => [
            "12\n345\n678",
            8,
            10,
            $this->createRange(2, 1, 2, 3)
        ];
    }

    private function createRange(int $line1, int $offset1, int $line2, int $offset2): Range
    {
        return new Range(new Position($line1, $offset1), new Position($line2, $offset2));
    }
}
