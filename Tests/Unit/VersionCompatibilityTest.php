<?php

declare(strict_types=1);

namespace Mediadreams\MdFullcalendar\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversNothing]
final class VersionCompatibilityTest extends UnitTestCase
{
    #[Test]
    public function currentVersionIsSupported(): void
    {
        self::assertContains(
            (new Typo3Version())->getMajorVersion(),
            [13, 14],
        );
    }
}
