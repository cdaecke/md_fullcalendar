<?php

declare(strict_types=1);

namespace Mediadreams\MdFullcalendar\Tests\Functional\Hooks;

use Mediadreams\MdFullcalendar\Hooks\TemplateLayouts;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversClass(TemplateLayouts::class)]
final class TemplateLayoutsTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'lochmueller/calendarize',
        'mediadreams/md_fullcalendar',
    ];

    private TemplateLayouts $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/Fixtures/Pages.csv');
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->create('en');
        $this->subject = GeneralUtility::makeInstance(TemplateLayouts::class);
    }

    #[Test]
    public function getTemplateLayoutsAppendsConfiguredAndTranslatedItems(): void
    {
        $config = [
            'effectivePid' => 1,
            'items' => [
                ['label' => 'Default', 'value' => ''],
            ],
        ];

        $this->subject->getTemplateLayouts($config);

        self::assertSame([
            ['label' => 'Default', 'value' => ''],
            ['label' => 'Plain layout', 'value' => '10'],
            ['label' => 'FullCalendar for ext:calendarize', 'value' => '20'],
        ], $config['items']);
    }

    #[Test]
    public function getTemplateLayoutsKeepsItemsUnchangedForPageWithoutConfiguration(): void
    {
        $items = [
            ['label' => 'Default', 'value' => ''],
        ];
        $config = [
            'effectivePid' => 3,
            'items' => $items,
        ];

        $this->subject->getTemplateLayouts($config);

        self::assertSame($items, $config['items']);
    }

    #[Test]
    public function getTemplateLayoutsKeepsInvalidConfigurationUnchanged(): void
    {
        $config = [
            'effectivePid' => '1',
            'items' => 'not an array',
        ];

        $this->subject->getTemplateLayouts($config);

        self::assertSame([
            'effectivePid' => '1',
            'items' => 'not an array',
        ], $config);
    }
}
