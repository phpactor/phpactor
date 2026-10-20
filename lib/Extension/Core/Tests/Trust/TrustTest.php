<?php

namespace Phpactor\Extension\Core\Tests\Trust;

use Phpactor\Extension\Core\Trust\Trust;
use PHPUnit\Framework\TestCase;

final class TrustTest extends TestCase
{
    public function testExactPath(): void
    {
        $trust = new Trust(['/home/me/project' => true], null);

        self::assertTrue($trust->hasTrust('/home/me/project'));
        self::assertTrue($trust->isTrusted('/home/me/project'));
    }

    public function testExactPathNotTrusted(): void
    {
        $trust = new Trust(['/home/me/project' => false], null);

        self::assertTrue($trust->hasTrust('/home/me/project'));
        self::assertFalse($trust->isTrusted('/home/me/project'));
    }

    public function testUnknownPathIsNotTrusted(): void
    {
        $trust = new Trust(['/home/me/project' => true], null);

        self::assertFalse($trust->hasTrust('/home/me/other'));
        self::assertFalse($trust->isTrusted('/home/me/other'));
    }

    public function testWildcardPattern(): void
    {
        $trust = new Trust(['/home/me/projects/*' => true], null);

        self::assertTrue($trust->hasTrust('/home/me/projects/one'));
        self::assertTrue($trust->isTrusted('/home/me/projects/one'));
        self::assertFalse($trust->isTrusted('/home/me/other/one'));
    }

    public function testWildcardMatchesNestedDirectories(): void
    {
        $trust = new Trust(['/home/me/projects/*' => true], null);

        self::assertTrue($trust->isTrusted('/home/me/projects/one/two'));
    }

    public function testWildcardPatternCanExclude(): void
    {
        $trust = new Trust(['/home/me/projects/secret*' => false], null);

        self::assertTrue($trust->hasTrust('/home/me/projects/secret'));
        self::assertFalse($trust->isTrusted('/home/me/projects/secret'));
    }

    public function testExactPathTakesPrecedenceOverPattern(): void
    {
        $trust = new Trust([
            '/home/me/projects/*' => true,
            '/home/me/projects/secret' => false,
        ], null);

        self::assertFalse($trust->isTrusted('/home/me/projects/secret'));
        self::assertTrue($trust->isTrusted('/home/me/projects/public'));
    }

    public function testMostSpecificPatternWins(): void
    {
        $trust = new Trust([
            '/home/me/projects/*' => true,
            '/home/me/projects/secret/*' => false,
        ], null);

        self::assertFalse($trust->isTrusted('/home/me/projects/secret/one'));
        self::assertTrue($trust->isTrusted('/home/me/projects/public/one'));
    }

    public function testQuestionMarkIsNotAWildcard(): void
    {
        $trust = new Trust(['/home/me/project?' => true], null);

        self::assertFalse($trust->isTrusted('/home/me/project1'));
    }

    public function testQuestionMarkIsNotAWildcardWhenCombinedWithAsterisk(): void
    {
        $trust = new Trust(['/home/me/*foo?' => true], null);

        self::assertFalse($trust->isTrusted('/home/me/xfooo'));
        self::assertTrue($trust->isTrusted('/home/me/xfoo?'));
    }

    public function testHomeDirectoryIsExpanded(): void
    {
        $home = getenv('HOME');
        putenv('HOME=/home/me');

        try {
            $trust = new Trust(['~/projects/*' => true], null);

            self::assertTrue($trust->isTrusted('/home/me/projects/one'));
            self::assertFalse($trust->isTrusted('/home/other/projects/one'));
        } finally {
            putenv(false === $home ? 'HOME' : 'HOME=' . $home);
        }
    }

    public function testExactPathWithHomeDirectory(): void
    {
        $home = getenv('HOME');
        putenv('HOME=/home/me');

        try {
            $trust = new Trust(['~/projects/one' => true], null);

            self::assertTrue($trust->isTrusted('/home/me/projects/one'));
            self::assertFalse($trust->isTrusted('/home/me/projects/two'));
        } finally {
            putenv(false === $home ? 'HOME' : 'HOME=' . $home);
        }
    }

    public function testMatchingUntrustedPatternCountsAsADecision(): void
    {
        $trust = new Trust(['*' => false], null);

        self::assertTrue($trust->hasTrust('/anything'));
        self::assertFalse($trust->isTrusted('/anything'));
    }

    public function testUnconditionalTrust(): void
    {
        putenv('PHPACTOR_UNCONDITIONAL_TRUST=1');

        try {
            $trust = new Trust([], null);
            self::assertTrue($trust->isTrusted('/anything'));
        } finally {
            putenv('PHPACTOR_UNCONDITIONAL_TRUST');
        }
    }
}
