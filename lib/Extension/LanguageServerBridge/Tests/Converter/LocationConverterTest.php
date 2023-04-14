<?php

namespace Phpactor\Extension\LanguageServer\Tests\ridge\Converter;

use Generator;
use Phpactor\TextDocument\FilesystemTextDocumentLocator;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\Extension\LanguageServerBridge\Converter\LocationConverter;
use Phpactor\Extension\LanguageServerBridge\Tests\IntegrationTestCase;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\Locations;
use Phpactor\LanguageServerProtocol\Location as LspLocation;

class LocationConverterTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function testConvertsPhpactorLocationsToLspLocations(): void
    {
        $this->workspace()->put('test.php', '012345678');

        $locations = new Locations([
            Location::fromPathAndOffset(
                $this->workspace()->path('test.php'),
                2
            )
        ]);

        $expected = [
            new LspLocation('file://' . $this->workspace()->path('test.php'), new Range(
                new Position(0, 2),
                new Position(0, 2),
            ))
        ];

        $converter = new LocationConverter(new FilesystemTextDocumentLocator());

        self::assertEquals($expected, $converter->toLspLocations($locations));
    }

    public function testIgnoresNonExistingFiles(): void
    {
        $this->workspace()->put('test.php', '12345678');

        $locations = new Locations([
            Location::fromPathAndOffset($this->workspace()->path('test.php'), 2),
            Location::fromPathAndOffset($this->workspace()->path('test-no.php'), 2)
        ]);

        $expected = [
            new LspLocation('file://' . $this->workspace()->path('test.php'), new Range(
                new Position(0, 2),
                new Position(0, 2),
            ))
        ];

        $converter = new LocationConverter(new FilesystemTextDocumentLocator());
        self::assertEquals($expected, $converter->toLspLocations($locations));
    }

    /**
     * @dataProvider provideDiskLocations
     * @dataProvider provideMultibyte
     * @dataProvider provideOutOfRange
     */
    public function testLocationToLspLocation(string $text, int $offset, Range $expectedRange): void
    {
        $this->workspace()->put('test.php', $text);

        $location = Location::fromPathAndOffset($this->workspace()->path('test.php'), $offset);

        $uri = 'file://' . $this->workspace()->path('test.php');

        $expected = new LspLocation($uri, $expectedRange);

        $converter = new LocationConverter(new FilesystemTextDocumentLocator());
        self::assertEquals($expected, $converter->toLspLocation($location));
    }

    /**
     * @return Generator<mixed>
     */
    public function provideOutOfRange(): Generator
    {
        yield 'out of upper range' => [
            '12345',
            10,
            $this->createRange(0, 5, 0, 5)
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideMultibyte(): Generator
    {
        yield '4 byte char 1st char' => [
            '😼😼😼😼😼',
            4,
            $this->createRange(0, 4, 0, 4)
        ];

        yield '4 byte char 2nd char' => [
            '😼😼😼😼😼',
            5,
            $this->createRange(0, 8, 0, 8)
        ];

        yield '4 byte char 4th char' => [
            '😼😼😼😼😼',
            16,
            $this->createRange(0, 16, 0, 16)
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideDiskLocations(): Generator
    {
        yield 'single line' => [
            '12345678',
            2,
            $this->createRange(0, 2, 0, 2)
        ];

        yield 'second line' => [
            "12\n345\n678",
            4,
            $this->createRange(1, 1, 1, 1)
        ];

        yield 'third line first char' => [
            "12\n345\n678",
            8,
            $this->createRange(2, 1, 2, 1)
        ];
    }

    private function createRange(int $line1, int $offset1, int $line2, int $offset2): Range
    {
        return new Range(new Position($line1, $offset1), new Position($line2, $offset2));
    }
}
