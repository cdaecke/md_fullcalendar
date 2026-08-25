<?php

declare(strict_types=1);

namespace Mediadreams\MdFullcalendar\Tests\Functional;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversNothing]
final class ExtensionLoadedTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = ['install'];

    protected array $testExtensionsToLoad = [
        'lochmueller/calendarize',
        'mediadreams/md_fullcalendar',
    ];

    protected bool $initializeDatabase = false;

    #[Test]
    public function isLoaded(): void
    {
        self::assertTrue(ExtensionManagementUtility::isLoaded('md_fullcalendar'));
    }
}
